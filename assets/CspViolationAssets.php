<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\assets;

use humhub\assets\CoreApiAsset;
use yii\web\AssetBundle;
use yii\web\View;

class CspViolationAssets extends AssetBundle
{
    public $sourcePath = '@onlyoffice/resources/js';

    public $js = [
        'humhub.onlyoffice.cspViolation.js'
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD
    ];

    public $depends = [
        CoreApiAsset::class
    ];
}
