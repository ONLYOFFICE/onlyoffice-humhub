<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\widgets;

use Yii;
use yii\helpers\Url;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\widgets\JsWidget;

class ConvertWidget extends JsWidget
{
    /**
     * @var File the file
     */
    public $file;

    /**
     * @inheritdoc
     */
    public $newName;

    /**
     * @inheritdoc
     */
    public $jsWidget = 'onlyoffice.Convert';

    /**
     * @inheritdoc
     */
    public $init = true;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $module = Yii::$app->getModule('onlyoffice');
        $this->newName = substr(
            $this->file->fileName,
            0,
            strpos($this->file->fileName, '.') + 1
        ) . $module->formats()->convertsTo[strtolower(FileHelper::getExtension($this->file))];
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        return [
            'convert-post' => Url::to([
                '/onlyoffice/convert/convert',
                'guid' => $this->file->guid,
                'ts' => time(),
                'newName' => $this->newName
            ]),
            'file-info-url' => Url::to(['/onlyoffice/open/get-info', 'guid' => $this->file->guid]),
            'done-message' => Yii::t('OnlyofficeModule.base', 'Done!'),
            'error-message' => Yii::t('OnlyofficeModule.base', 'Error:'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        return $this->render('convert', [
                    'options' => $this->getOptions(),
                    'file' => $this->file,
                    'newName' => $this->newName,
        ]);
    }
}
