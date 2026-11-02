<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\assets;

use humhub\assets\CoreApiAsset;
use yii\web\AssetBundle;

class NewTabAssets extends AssetBundle
{
    public $sourcePath = '@onlyoffice/resources/js';

    public $js = [
        'humhub.onlyoffice.newTab.js'
    ];

    public $depends = [
        CoreApiAsset::class
    ];
}
