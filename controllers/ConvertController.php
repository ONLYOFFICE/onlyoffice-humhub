<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\onlyoffice\components\BaseFileController;
use \humhub\components\Module;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;

class ConvertController extends BaseFileController
{

    /**
     * @var Module
     */
    public $module;

    public function init()
    {
        parent::init();

        $this->module = Yii::$app->getModule('onlyoffice');

        if (!$this->module->canConvert($this->file)) {
            throw new HttpException('400', 'Could not convert this file');
        }
    }

    public function actionIndex()
    {
        return $this->renderAjax('index', ['file' => $this->file]);
    }

    public function actionConvert($guid, $ts, $newName)
    {
        $result = $this->conversion($this->file->onlyoffice_key . $ts, true);

        if (isset($result['endConvert']) && $result['endConvert']) {
            $this->saveFileReplace($result['fileUrl'], $newName);
        }

        return $result;
    }

    public function actionDownload()
    {
        $key = $this->module->generateDocumentKey($this->file);
        $forcesave = $this->module->commandService(['c' => 'forcesave', 'key' => $key]);
        if($forcesave['error'] !== 0 && $forcesave['error'] !== 4) {
            throw new HttpException('400', 'Could not force save this file');
        }

        $result = $this->conversion($key . time(), false);

        if (isset($result['endConvert']) && $result['endConvert']) {
            return Yii::$app->response->redirect($result['fileUrl']);
            // $file = $this->saveNewFile($result['fileUrl'], $result['fileType']);
            // $downloadUrl = $this->module->getDownloadUrl($file);
            // return Yii::$app->response->redirect($downloadUrl);
        }
    }

    private function conversion($key, $async)
    {
        Yii::$app->response->format = 'json';

        $fromExt = strtolower(FileHelper::getExtension($this->file));
        $toExt = $this->module->convertsTo[$fromExt];

        $downloadUrl = $this->module->getDownloadUrl($this->file);
        $result = $this->module->convertService($downloadUrl, $fromExt, $toExt, $key, $async);
        return $result;
    }

    private function saveFileReplace($url, $newName)
    {
        $content = $this->module->request($url)->getContent();

        $this->file->store->setContent($content);
        $this->file->updateAttributes([
            'onlyoffice_key' => new \yii\db\Expression('NULL'),
            'size' => strlen($content),
            'file_name' => $newName
        ]);
    }

    private function saveNewFile($url, $newExt)
    {
        $newName = substr($this->file->fileName, 0, strpos($this->file->fileName, '.') + 1) . $newExt;
        $content = $this->module->request($url)->getContent();

        $file = new File();
        $file->file_name = $newName;
        $file->size = mb_strlen($content, '8bit');
        $file->mime_type = $this->module->mimes['oform'];
        $file->save();
        $file->getStore()->setContent($content);
        return $file;
    }
}
