<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\libs\Html;
use humhub\widgets\ModalDialog;

if (class_exists('humhub\assets\ClipboardJsAsset')) {
    humhub\assets\ClipboardJsAsset::register($this);
}
?>

<?php $modal = ModalDialog::begin() ?>
<?= Html::beginTag('div', $options) ?>

<div class="modal-header">
    <h4 class="modal-title" id="#onlyoffice-share-modal-title">
        <?= Yii::t('OnlyofficeModule.base', '<strong>Share</strong> document'); ?>
    </h4>
</div>
<div class="modal-body">
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
    <div class="form-group viewLinkInput" style="margin-top:6px">
        <input type="text" class="form-control" value="<?= $viewLink; ?>">
        <p class="help-block pull-right">
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
    <div class="form-group editLinkInput"  style="margin-top:6px">
        <input type="text" class="form-control" value="<?= $editLink; ?>">
        <p class="help-block  pull-right">
            <a href="#" onClick="clipboard.writeText($('.editLinkInput').find('input').val())">
                <i class="fa fa-clipboard" aria-hidden="true"></i>
                <?= Yii::t('OnlyofficeModule.base', 'Copy to clipboard'); ?>
            </a>
        </p>
    </div>

</div>

<div class="modal-footer">
    <a href="#" data-modal-close class="btn btn-primary" data-ui-loader>
        <?= Yii::t('OnlyofficeModule.base', 'Close'); ?>
    </a>
</div>

<?= Html::endTag('div'); ?>
<?php ModalDialog::end(); ?>