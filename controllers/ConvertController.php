<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\onlyoffice\components\BaseFileController;
use humhub\components\Module;
use humhub\modules\onlyoffice\services\FileService;

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

        if (!$this->file->canDelete()) {
            throw new HttpException(403, Yii::t('OnlyofficeModule.base', 'File write access denied!'));
        }

        if (!FileService::instance($this->file)->canConvert()) {
            throw new HttpException('400', 'Could not convert this file');
        }
    }

    public function actionIndex()
    {
        return $this->renderAjax('index', ['file' => $this->file]);
    }

    public function actionConvert($guid, $ts)
    {
        Yii::$app->response->format = 'json';

        $result = FileService::instance($this->file)->convert($ts);

        if (isset($result['endConvert']) && $result['endConvert']) {
            $this->saveFileReplace($result['fileUrl'], FileService::instance($this->file)->getConvertedName());
        }

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
}
