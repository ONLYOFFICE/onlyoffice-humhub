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

use Yii;
use yii\helpers\Url;
use humhub\modules\onlyoffice\Module;
use humhub\modules\file\handler\BaseFileHandler;

/**
 * Description of ViewHandler
 *
 * @author Luke
 */
class ViewFileHandler extends BaseFileHandler
{
    /**
     * @inheritdoc
     */
    public function getLinkAttributes()
    {
        $openInNewTab = Yii::$app->getModule('onlyoffice')->getOpenInNewTab();
        $label = Yii::t('OnlyofficeModule.base', 'View document');
        $url = Url::to([
            '/onlyoffice/open',
            'guid' => $this->file->guid,
            'mode' => Module::OPEN_MODE_VIEW
        ]);

        if ($openInNewTab) {
            return [
                'label' => $label,
                'href' => $url,
                'target' => '_blank',
            ];
        }

        return [
            'label' => $label,
            'data-action-url' => $url,
            'data-action-click' => 'ui.modal.load',
            'data-modal-id' => 'onlyoffice-modal',
            'data-modal-close' => ''
        ];
    }
}
