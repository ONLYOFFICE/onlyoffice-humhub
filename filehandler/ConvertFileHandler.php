<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\filehandler;

use Yii;
use yii\helpers\Url;
use humhub\modules\file\handler\BaseFileHandler;

class ConvertFileHandler extends BaseFileHandler
{
    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        return [
            'label' => Yii::t('OnlyofficeModule.base', 'Convert document'),
            'data-action-url' => Url::to(['/onlyoffice/convert', 'guid' => $this->file->guid]),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlyoffice-modal',
            'data-modal-close' => ''
        ];
    }
}
