<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\helpers\Html;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

if (class_exists('humhub\assets\ClipboardJsAsset')) {
    humhub\assets\ClipboardJsAsset::register($this);
}
?>

<?php Modal::beginDialog([
    'title' => Yii::t('OnlyofficeModule.base', '<strong>Share</strong> document'),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

    <?= Html::beginTag('div', $options) ?>

        <?= Yii::t(
            'OnlyofficeModule.base',
            'You can simply share this document using a direct link. ' .
                'The user does not need an valid user account on the platform.'
        ); ?>
        <br/>
        <br/>

        <div class="checkbox" style="margin-left:-10px;">
            <label>
                <input type="checkbox" class="viewLinkCheckbox">
                <?= Yii::t('OnlyofficeModule.base', 'Link for view only access'); ?>
            </label>
        </div>
        <div class="mb-3 viewLinkInput" style="margin-top:6px">
            <input type="text" class="form-control" value="<?= $viewLink; ?>">
            <p class="form-text float-end">
                <a href="#" onClick="clipboard.writeText($('.viewLinkInput').find('input').val())">
                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                    <?= Yii::t('OnlyofficeModule.base', 'Copy to clipboard'); ?>
                </a>
            </p>
        </div>

        <div class="checkbox" style="margin-left:-10px;padding-top:12px">
            <label>
                <input type="checkbox" class="editLinkCheckbox">
                <?= Yii::t('OnlyofficeModule.base', 'Link with enabled write access'); ?>
            </label>
        </div>
        <div class="mb-3 editLinkInput"  style="margin-top:6px">
            <input type="text" class="form-control" value="<?= $editLink; ?>">
            <p class="form-text  float-end">
                <a href="#" onClick="clipboard.writeText($('.editLinkInput').find('input').val())">
                    <i class="fa fa-clipboard" aria-hidden="true"></i>
                    <?= Yii::t('OnlyofficeModule.base', 'Copy to clipboard'); ?>
                </a>
            </p>
        </div>

    <?= Html::endTag('div'); ?>

<?php Modal::endDialog() ?>
