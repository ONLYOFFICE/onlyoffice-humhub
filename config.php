<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\Events;

$config = [
    'id' => 'onlyoffice',
    'class' => 'humhub\modules\onlyoffice\Module',
    'namespace' => 'humhub\modules\onlyoffice',
    'events' => [
        [
            FileHandlerCollection::className(),
            FileHandlerCollection::EVENT_INIT,
            ['humhub\modules\onlyoffice\Events', 'onFileHandlerCollection']
        ],
    ]
];

if (defined('humhub\modules\file\models\File::EVENT_AFTER_NEW_STORED_FILE')) {
    array_push(
        $config['events'],
        [File::class, File::EVENT_AFTER_NEW_STORED_FILE, [Events::class, 'onAfterNewStoredFile']]
    );
};

return $config;
