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

namespace humhub\modules\onlyoffice;

use Yii;
use yii\db\Expression;
use humhub\modules\file\handler\FileHandlerCollection;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\permissions\CanUseOnlyOffice;
use humhub\modules\onlyoffice\services\FileService;
use humhub\modules\cfiles\permissions\ManageFiles;
use humhub\modules\content\models\ContentContainer;

/**
 * @author luke
 */
class Events
{
    public static function onFileHandlerCollection($event)
    {
        if (!Yii::$app->user->can(CanUseOnlyOffice::class)) {
            return;
        }

        /* @var $collection FileHandlerCollection */
        $collection = $event->sender;

        $isCfiles = Yii::$app->controller->module && Yii::$app->controller->module->id == 'cfiles';
        if ($isCfiles && $collection->type === FileHandlerCollection::TYPE_CREATE && !self::checkCFilesPermission()) {
            return;
        }

        if ($collection->type === FileHandlerCollection::TYPE_CREATE) {
            $collection->register(new filehandler\CreateFileHandler());
            return;
        }

        $file = $event->sender->file;
        if (!$file instanceof File) {
            return;
        }

        $fileService = FileService::instance($file);

        if ($fileService->getDocumentType() === null) {
            return;
        }

        $isOnlyofficeForm = $fileService->isOnlyofficeForm();

        $canEdit = $collection->type == FileHandlerCollection::TYPE_EDIT && $fileService->canEdit();
        $canConvert = $collection->type == FileHandlerCollection::TYPE_EDIT && $fileService->canConvert();
        $canView = $collection->type == FileHandlerCollection::TYPE_VIEW && $fileService->canView();

        if ($canEdit) {
            $collection->register(new filehandler\EditFileHandler());
        }
        if ($canConvert) {
            $collection->register(new filehandler\ConvertFileHandler());
        }
        if ($canView && $isOnlyofficeForm) {
            $collection->register(new filehandler\FillingFileHandler());
            return;
        }
        if ($canView) {
            $collection->register(new filehandler\ViewFileHandler());
        }
    }

    public static function onAfterNewStoredFile($event)
    {
        $file = $event->sender;

        if ($file instanceof File && isset($file->onlyoffice_key)) {
            if (!$file->onlyoffice_key_lock) {
                $file->updateAttributes(['onlyoffice_key' => new Expression('NULL')]);
            }
        }
    }

    private static function checkCFilesPermission(): bool
    {
        $guid = Yii::$app->request->get('cguid');
        $container = ContentContainer::findOne(['guid' => $guid]);
        $object = $container ? $container->getPolymorphicRelation() : null;
        return Yii::$app->hasModule('cfiles') && $object && $object->can(ManageFiles::class);
    }
}
