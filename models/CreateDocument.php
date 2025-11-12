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

namespace humhub\modules\onlyoffice\models;

use Yii;
use yii\base\Model;
use humhub\modules\file\models\File;
use humhub\modules\cfiles\models\Folder;
use humhub\modules\cfiles\permissions\WriteAccess;

/**
 * Description of CreateDocument
 *
 * @author Luke
 */
class CreateDocument extends Model
{
    public $extension;
    public $fileName;
    public $fid;
    public $openFlag = true;

    public function rules()
    {
        return [
            ['fileName', 'required'],
            ['openFlag', 'boolean'],
            ['fid', 'string'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fileName' => Yii::t('OnlyofficeModule.base', 'File Name'),
            'openFlag' => Yii::t('OnlyofficeModule.base', 'Open the new document in the next step')
        ];
    }

    public function save()
    {
        if (empty($this->extension)) {
            throw new Exception("File extension cannot be empty");
        }

        $cfiles = Yii::$app->getModule('cfiles');
        $folder = isset($cfiles) ? Folder::findOne($this->fid) : null;
        if ($folder && !$folder->content->container->permissionManager->can(WriteAccess::class)) {
            return false;
        }

        if ($this->validate()) {
            $module = Yii::$app->getModule('onlyoffice');

            $source = $this->templatePath() . '/new.' . $this->extension;
            $newFile = $this->fileName . '.' . $this->extension;

            $mime = $module->formats()->mimes[$this->extension];

            $file = new File();
            $file->file_name = $newFile;
            $file->size = filesize($source);
            $file->mime_type = $mime;
            $file->save();
            $file->store->setContent(file_get_contents($source));

            return $file;
        }

        return false;
    }

    private function templatePath()
    {
        $module = Yii::$app->getModule('onlyoffice');
        $user = Yii::$app->user->getIdentity();

        $lang = ($user) && !empty($user->language) ? $user->language : Yii::$app->language;
        if (!array_key_exists($lang, $module->languageCodes)) {
            $lang = 'default';
        }

        return $module->getAssetPath() . '/templates/' . $module->languageCodes[$lang];
    }
}
