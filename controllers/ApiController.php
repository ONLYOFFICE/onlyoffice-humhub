<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use Exception;
use humhub\components\access\ControllerAccess;
use yii\helpers\Url;
use humhub\components\Controller;
use humhub\components\Module;
use humhub\modules\file\models\File;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\user\models\User;
use humhub\modules\onlyoffice\notifications\Mention as Notify;
use humhub\modules\onlyoffice\models\Mention;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\permissions\ManageContent;
use yii\base\DynamicModel;
use yii\web\HttpException;

class ApiController extends Controller
{
    /**
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY => ['users-for-mentions']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->module = Yii::$app->getModule('onlyoffice');

        return parent::beforeAction($action);
    }

    /**
     * Saveas action
     */
    public function actionSaveas()
    {
        $url = Yii::$app->request->post('url');
        $filename = Yii::$app->request->post('name');

        if (parse_url($url, PHP_URL_HOST) !== parse_url($this->module->getServerUrl(), PHP_URL_HOST)) {
            throw new Exception("Incorrect domain in file url");
        }

        $url = $this->module->replaceDocumentServerUrlToInternal($url);

        $response = $this->module->request($url);

        $newContent = $response->getContent();
        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

        $file = new File();
        $file->file_name = $filename;
        $file->size = mb_strlen($newContent, '8bit');
        $file->mime_type = isset($this->module->formats()->mimes[$fileExt])
            ? $this->module->formats()->mimes[$fileExt]
            : 'application/octet-stream';
        $file->save();
        $file->getStore()->setContent($newContent);

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }

    public function actionUsersForMentions()
    {
        $model = DynamicModel::validateData(
            [
                'key' => Yii::$app->request->post('key', ''),
                'offset' => Yii::$app->request->post('offset', 0),
                'limit' => Yii::$app->request->post('limit', 100),
                'search' => Yii::$app->request->post('search', ''),
            ],
            [
                ['key', 'required'],
                [['key', 'search'], 'string', 'max' => 255],
                [['offset', 'limit'], 'integer', 'min' => 0],
            ]
        );

        if ($model->hasErrors()) {
            throw new HttpException(400);
        }

        $offset = (int) $model->offset;
        $limit = (int) $model->limit;
        $search = strtolower(trim($model->search));

        $file = File::findOne(['onlyoffice_key' => $model->key]);

        if ($file === null || !$file->canDelete()) {
            throw new HttpException(403);
        }

        return $this->asJson([
            'users' => $this->getUsersForMentions($file, $offset, $limit, $search)
        ]);
    }

    public function actionMakeAnchor()
    {
        $doc_key = Yii::$app->request->post('doc_key');

        $file = File::findOne(['onlyoffice_key' => $doc_key]);
        $url = Url::to(['/onlyoffice/open', 'guid' => $file->guid, 'mode' => 'view']);

        return $this->asJson([
            'url' => $url
        ]);
    }

    public function actionSendNotify()
    {
        $emails = Yii::$app->request->post('emails');
        $message = Yii::$app->request->post('comment');
        $anchor = Yii::$app->request->post('ACTION_DATA');
        $doc_key = Yii::$app->request->post('doc_key');

        $originator = Yii::$app->user->getIdentity();
        $users = User::find()->where(['email' => $emails])->all();

        $file = File::findOne(['onlyoffice_key' => $doc_key]);

        $mention = Mention::generateMention($file, $message, $anchor);

        try {
            Notify::instance()->from($originator)->about($mention)->sendBulk($users);
        } catch (Exception $exception) {
            throw new Exception("Mention error.");
        }

        return $this->asJson([]);
    }

    /**
     * Rename action
     */
    public function actionRename()
    {
        $key = Yii::$app->request->post('key');
        $newFileName = Yii::$app->request->post('newFileName');
        $ext = Yii::$app->request->post('ext');

        $file = File::findOne(['onlyoffice_key' => $key]);

        $owner = User::findOne($file->created_by);
        $containerRecord = ContentContainer::findOne(['id' => $owner->contentcontainer_id]);
        $container = $containerRecord->getPolymorphicRelation();
        $canRename = $container->can(ManageContent::class);
        if (!$canRename) {
            throw new \Exception('Permission denied');
        }

        $arrayName = explode(".", $newFileName);
        $curExt = end($arrayName);

        if ($ext !== $curExt) {
            $newFileName .= "." . $ext;
        }

        $file->updateAttributes(['file_name' => $newFileName]);

        $meta = [
            "c" => "meta",
            "key" => $key,
            "meta" => [
                "title" => $newFileName
            ]
        ];
        $response = $this->module->commandService($meta);

        if ($response['error'] !== 0) {
            throw new \Exception('Error from command Service: ' . $response['error']);
        }

        return $this->asJson([
            'file' => FileHelper::getFileInfos($file)
        ]);
    }

    private function getUsersForMentions($file, $offset = 0, $limit = 100, $search = '')
    {
        $curUser = Yii::$app->user->getIdentity();
        $users = [];

        $userQuery = User::find()
            ->andWhere(['not', ['email' => $curUser->email]])
            ->available()
            ->search($search)
            ->orderBy(['id' => SORT_DESC]);

        $filteredOffset = 0;
        foreach ($userQuery->batch(1000) as $userBatch) {
            $filteredUsers = array_filter($userBatch, fn(User $user) => $file->canRead($user));
            $filteredCount = count($filteredUsers);
            if ($filteredOffset + $filteredCount > $offset) {
                $users = array_merge($users, array_slice($filteredUsers, $offset - $filteredOffset));
            } else {
                $filteredOffset += $filteredCount;
            }
            if (count($users) >= $limit) {
                break;
            }
        }

        $users = array_map(
            fn(User $user) => [
                "name" => $user->profile->firstname . " " . $user->profile->lastname,
                "email" => $user->email
            ],
            array_slice($users, 0, $limit),
        );

        return $users;
    }
}
