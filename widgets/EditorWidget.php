<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\widgets;

use Yii;
use yii\web\HttpException;
use humhub\modules\file\models\File;
use humhub\modules\cfiles\models\File as cFile;
use humhub\modules\cfiles\models\Folder as cFolder;
use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\file\libs\FileHelper;
use humhub\widgets\JsWidget;
use \Firebase\JWT\JWT;

/**
 * Description of EditorWidget
 *
 * @author Luke
 */
class EditorWidget extends JsWidget
{

    /**
     * @var File the file
     */
    public $file;

    /**
     * @var string mode (edit or view)
     */
    public $mode;

    /**
     * @inheritdoc
     */
    public $jsWidget = 'onlyoffice.Editor';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     */
    protected $documentType = null;

    /**
     * Mobile regex from https://github.com/ONLYOFFICE/CommunityServer/blob/v9.1.1/web/studio/ASC.Web.Studio/web.appsettings.config#L35
     */
    const USER_AGENT_MOBILE = "/android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\/|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i";

    /**
     * @inheritdoc
     */
    public function init()
    {
        $module = Yii::$app->getModule('onlyoffice');
        $this->documentType = $module->getDocumentType($this->file);
        if ($this->documentType === null) {
            throw new HttpException('400', 'Requested file type is not supported!');
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $module = Yii::$app->getModule('onlyoffice');

        $api = [];
        if ($this->file->object_model === cFile::class) {
            $cfile = cFile::findOne($this->file->object_id);
            $cfolder = cFolder::findOne($cfile->parent_folder_id);
            if (!$cfolder->isAllPostedFiles()) {
                $api['saveasUrl'] = Url::to(['/onlyoffice/api/saveas'], true);
            }
        }

        return [
            'config' => $this->getConfig(),
            'edit-mode' => $this->mode,
            'file-info-url' => Url::to(['/onlyoffice/open/get-info', 'guid' => $this->file->guid]),
            'module-configured' => (empty($module->getServerUrl()) ? '0' : '1'),
            'api' => $api,
            'download-post' => Url::to(['/onlyoffice/convert/download', 'guid' => $this->file->guid]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return [
            'style' => 'height:100%;border-radius: 8px 8px 0px 0px;background-color:#F4F4F4'
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('editor', [
                    'documentType' => $this->documentType,
                    'file' => $this->file,
                    'mode' => $this->mode,
                    'options' => $this->getOptions(),
        ]);
    }

    protected function getConfig()
    {
        $module = Yii::$app->getModule('onlyoffice');
        $user = Yii::$app->user->getIdentity();
        $userGuid = null;
        if (isset($user->guid)) {
            $userGuid = $user->guid;
        }
        $key = $module->generateDocumentKey($this->file);
        $docHash = $module->generateHash($key, $userGuid);

        $url = Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], true);
        $callbackUrl = Url::to(['/onlyoffice/backend/track', 'doc' => $docHash], true);
        if (!empty($module->getStorageUrl())) {
            $url = $module->getStorageUrl() . Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], false);
            $callbackUrl = $module->getStorageUrl() . Url::to(['/onlyoffice/backend/track', 'doc' => $docHash], false);
        }

        $config = [
            'type' => 'desktop',
            'documentType' => $this->documentType,
            'document' => [
                'title' => Html::encode($this->file->fileName),
                'url' => $url,
                'fileType' => Html::encode(strtolower(FileHelper::getExtension($this->file))),
                'key' => $key,
                'info' => [
                    'author' => Html::encode($this->file->createdBy->displayname),
                    'created' => Html::encode(Yii::$app->formatter->asDatetime($this->file->created_at, 'short')),
                ],
                'permissions' => [
                    'edit' => $this->mode == 'edit',
                ]
            ],
            'editorConfig' => [
                'mode' => $this->mode,
                'lang' => ($user) && !empty($user->language) ? $user->language : Yii::$app->language,
                'callbackUrl' => $callbackUrl,
                'user' => [
                    'id' => ($user) ? Html::encode($user->guid) : '',
                    'name' => ($user) ? Html::encode($user->displayname) : 'Anonymous User',
                ],
                'customization' => [
                    //'forcesave' => true,
                    'chat' => boolval($module->getChat()),
                    'compactHeader' => boolval($module->getCompactHeader()),
                    'feedback' => boolval($module->getFeedback()),
                    'help' => boolval($module->getHelp()),
                    'compactToolbar' => boolval($module->getCompactToolbar()),
                ]
            ]
        ];

        $userAgent = Yii::$app->request->getUserAgent();
        if (preg_match($this::USER_AGENT_MOBILE, $userAgent)) {
            $config['type'] = 'mobile';
        }

        if ($module->isJwtEnabled()) {
            $token = JWT::encode($config, $module->getJwtSecret());
            $config['token'] = $token;
        }

        return $config;
    }
}
