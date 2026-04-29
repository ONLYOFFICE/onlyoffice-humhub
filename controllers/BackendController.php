<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use humhub\components\Controller;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;

class BackendController extends Controller
{
    /**
     * @inheritdoc
     * Allow server to server configuration without any authentication
     */
    public $access = \humhub\components\access\ControllerAccess::class;

    /**
     * @var File
     */
    public $file;

    /**
     * @var Module
     */
    public $module;

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $this->module = Yii::$app->getModule('onlyoffice');
        $this->enableCsrfValidation = false;

        $hash = Yii::$app->request->get('doc');

        list ($hashData, $error) = $this->module->readHash($hash);
        if (!empty($error)) {
            throw new HttpException(404, 'Backend action with empty or invalid hash');
        }

        $key = isset($hashData->key) ? $hashData->key : null;
        $userGuid = isset($hashData->userGuid) ? $hashData->userGuid : null;
        $isEmpty = isset($hashData->isEmpty) ? $hashData->isEmpty : false;

        $this->file = File::findOne(['onlyoffice_key' => $key]);

        if (Yii::$app->settings->get('maintenanceMode')) {
            $user = User::findOne(['guid' => $userGuid]);
            if (!empty($user) && $user->isSystemAdmin()) {
                Yii::$app->user->login($user);
            }
        }

        if ($this->file == null && !$isEmpty) {
            throw new HttpException(404, Yii::t('OnlyofficeModule.base', 'Could not find requested file!'));
        }

        return parent::beforeAction($action);
    }

    /**
     * Download function for the Only server
     */
    public function actionDownload()
    {
        if ($this->module->isJwtEnabled()) {
            $header = Yii::$app->request->headers->get($this->module->getHeader());
            if (!empty($header)) {
                $token = substr($header, strlen('Bearer '));
            }

            if (empty($token)) {
                throw new HttpException(403, 'Expected JWT');
            }

            try {
                $ds = $this->module->jwtDecode($token);
            } catch (\Exception $ex) {
                throw new HttpException(403, 'Invalid JWT signature');
            }
        }

        return Yii::$app->response->sendFile($this->file->store->get(), $this->file->file_name);
    }

    /**
     * Download empty file
     */
    public function actionEmptyFile()
    {
        if ($this->module->isJwtEnabled()) {
            $header = Yii::$app->request->headers->get($this->module->getHeader());
            if (!empty($header)) {
                $token = substr($header, strlen('Bearer '));
            }

            if (empty($token)) {
                throw new HttpException(403, 'Expected JWT');
            }

            try {
                $ds = $this->module->jwtDecode($token);
            } catch (\Exception $ex) {
                throw new HttpException(403, 'Invalid JWT signature');
            }
        }

        return Yii::$app->response->sendFile($this->module->getAssetPath() . '/templates/en-US/new.docx');
    }

    public function actionTrack()
    {
        Yii::$app->response->format = 'json';

        $_trackerStatus = array(
            0 => 'NotFound',
            1 => 'Editing',
            2 => 'MustSave',
            3 => 'Corrupted',
            4 => 'Closed',
            6 => 'ForceSave'
        );


        if (($body_stream = file_get_contents('php://input')) === false) {
            throw new HttpException(400, 'Empty body');
        }

        $data = json_decode($body_stream, true); //json_decode - PHP 5 >= 5.2.0
        if ($data === null) {
            throw new HttpException(400, 'Could not parse json');
        }

        if ($this->module->isJwtEnabled()) {
            $token = null;
            if (!empty($data["token"])) {
                $token = $data["token"];
            } else {
                $header = Yii::$app->request->headers->get($this->module->getHeader());
                if (!empty($header)) {
                    $token = substr($header, strlen('Bearer '));
                }
            }

            if (empty($token)) {
                throw new HttpException(403, 'Expected JWT');
            }

            try {
                $ds = $this->module->jwtDecode($token);
                $data = isset($ds->payload) ? (array)$ds->payload : (array)$ds;
            } catch (\Exception $ex) {
                throw new HttpException(403, 'Invalid JWT signature');
            }
        }

        $user = null;
        if (!empty($data['users'])) {
            $users = $data['users'];
            $user = User::findOne(['guid' => $users[0]]);
        }

        $result = [];
        $msg = null;
        $status = $_trackerStatus[$data["status"]];
        switch ($status) {
            case "MustSave":
            case "Corrupted":
            case "ForceSave":
                try {
                    $url = $data["url"];
                    $originExt = strtolower(FileHelper::getExtension($this->file));
                    $currentExt = strtolower($data['filetype']);

                    if ($originExt !== $currentExt) {
                        $convResult = $this->module->convertService(
                            $data["url"],
                            $currentExt,
                            $originExt,
                            $this->module->generateDocumentKey($this->file) . time(),
                            false
                        );
                        $url = $convResult['fileUrl'];
                    }

                    $newData = $this->module->request($url)->getContent();

                    if (!empty($newData)) {
                        if ($status == 'ForceSave') {
                            $this->file->updateAttributes(['onlyoffice_key_lock' => true]);
                        }

                        if (version_compare(Yii::$app->version, '1.10', '>=')) {
                            // For HumHub from version 1.10 with versioning support
                            $this->file->setStoredFileContent($newData);
                        } else {
                            // Older HumHub versions
                            $this->file->getStore()->setContent($newData);
                        }

                        $newAttr = [
                            'updated_at' => date("Y-m-d H:i:s"),
                            'size' => strlen($newData),
                        ];

                        if ($status != 'ForceSave') {
                            $newAttr['onlyoffice_key'] = new \yii\db\Expression('NULL');
                        }

                        if (!empty($user)) {
                            $newAttr['updated_by'] = $user->getId();
                        }
                        $this->file->updateAttributes($newAttr);
                    } else {
                        throw new \Exception('Could not save onlyoffice document: ' . $data["url"]);
                    }

                    break;
                } catch (\Exception $e) {
                    Yii::error($e->getMessage(), 'onlyoffice');
                    $msg = $e->getMessage();
                } finally {
                    if ($status == 'ForceSave') {
                        $this->file->updateAttributes(['onlyoffice_key_lock' => false]);
                    }
                }
        }

        if ($msg == null) {
            $result['error'] = 0;
        } else {
            Yii::$app->response->statusCode = 500;
            $result['error'] = 1;
            $result['message'] = $msg;
        }

        //Yii::warning("Return: " . print_r($result, 1), 'onlyoffice');
        return $result;
    }
}
