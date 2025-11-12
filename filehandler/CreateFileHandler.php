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

namespace humhub\modules\onlyoffice\filehandler;

use humhub\modules\file\handler\BaseFileHandler;
use Yii;
use yii\helpers\Url;

/**
 * Description of ViewHandler
 *
 * @author Luke
 */
class CreateFileHandler extends BaseFileHandler
{
    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        list (, $baseUrl) = Yii::$app->getAssetManager()->publish("@onlyoffice/resources/app-dark.svg");
        return [
            'label' => Yii::t(
                'OnlyofficeModule.base',
                '<img style="height: 14px; margin-right: 5px;" src="' . $baseUrl .
                '"/>Create file <small>(Text, Spreadsheet, Presentation, PDF form)</small>',
            ),
            'data-action-url' => Url::to(['/onlyoffice/create']),
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlyoffice-modal',
            'data-modal-close' => '',
        ];
    }
}
