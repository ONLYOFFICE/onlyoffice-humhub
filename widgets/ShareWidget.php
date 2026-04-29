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

namespace humhub\modules\onlyoffice\widgets;

use yii\helpers\Url;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\models\Share;
use humhub\modules\onlyoffice\Module;
use humhub\widgets\JsWidget;

/**
 * Description of EditorWidget
 *
 * @author Luke
 */
class ShareWidget extends JsWidget
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
    public $jsWidget = 'onlyoffice.Share';

    /**
     * @inheritdoc
     */
    public $init = true;

    /**
     * @inheritdoc
     */
    public function getData()
    {

        return [
            'share-remove-link' => Url::to(['/onlyoffice/share/remove', 'guid' => $this->file->guid, 'mode' => 'edit']),
            'share-get-link' => Url::to(['/onlyoffice/share/get', 'guid' => $this->file->guid, 'mode' => 'edit']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $viewLink = Share::getShareLink($this->file, false, Module::OPEN_MODE_VIEW);
        $editLink = Share::getShareLink($this->file, false, Module::OPEN_MODE_EDIT);

        return $this->render('share', [
                    'options' => $this->getOptions(),
                    'mode' => $this->mode,
                    'file' => $this->file,
                    'viewLink' => $viewLink,
                    'editLink' => $editLink,
        ]);
    }
}
