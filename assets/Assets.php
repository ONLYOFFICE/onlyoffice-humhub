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

namespace humhub\modules\onlyoffice\assets;

use Yii;
use yii\web\AssetBundle;

class Assets extends AssetBundle
{
    public $publishOptions = [
        'forceCopy' => true
    ];
    public $css = [];
    public $jsOptions = [
        'position' => \yii\web\View::POS_BEGIN
    ];

    public function init()
    {

        $this->js = [
            'humhub.onlyoffice.js'
        ];


        $this->sourcePath = dirname(__FILE__) . '/../resources';
        parent::init();
    }
}
