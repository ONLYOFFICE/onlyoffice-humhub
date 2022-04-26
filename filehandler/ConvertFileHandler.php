<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\filehandler;

use Yii;
use humhub\modules\file\handler\BaseFileHandler;
use yii\helpers\Url;

class ConvertFileHandler extends BaseFileHandler
{

    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        $attributes = [
            'label' => Yii::t('OnlyofficeModule.base', 'Convert document'),
            'data-action-url' => Url::to(['/onlyoffice/convert', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlyoffice-modal',
            'data-modal-close' => ''
        ];

        if (pathinfo($this->file->file_name, PATHINFO_EXTENSION) === 'docxf') {
            $attributes = [
                'label' => Yii::t('OnlyofficeModule.base', 'Fill in form in ONLYOFFICE'),
                'href' => Url::to(['/onlyoffice/convert/download', 'guid' => $this->file->guid]),
                'target' => "_blank"
            ];
        }

        return $attributes;
    }

}
