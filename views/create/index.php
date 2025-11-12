<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\helpers\Url;

$modal = \humhub\widgets\ModalDialog::begin([
            'header' => Yii::t('OnlyofficeModule.base', '<strong>Create</strong> document')
        ])
?>
<style>

    .modal-dialog {
        width: 800px;
    }
    .try-editor-list {
        list-style: none;
        margin: 0;
        padding: 0;
        height: 180px;
    }
    .try-editor-list li {
        float: left;
        cursor: pointer;
        border:1px solid #EEE;
        height: 150px;
        padding: 12px;
        margin: 25px;
        width: 135px;
    }
    .try-editor-list li:hover {
        background-color:#6FDBE8;
    }

    .try-editor {
        background-color: transparent;
        background-position: center 0;
        background-repeat: no-repeat;
        display: block;
        font-size: 14px;
        font-weight: bold;
        height: 45px;
        padding-top: 100px;
        text-align: center;
        text-decoration: none;
    }    
    .try-editor.document {
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_docx.svg'); ?>");
    }
    .try-editor.spreadsheet {
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_xlsx.svg'); ?>");
    }
    .try-editor.presentation {
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_pptx.svg'); ?>");
    }
    .try-editor.form-template {
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_pdf.svg'); ?>");
    }
</style>
<div class="modal-body">
    <br />
    <span class="try-descr"><?= Yii::t(
                'OnlyofficeModule.base',
                'Please select a document type.'
            ); ?></span>
    <br />
    <ul class="try-editor-list">
        <li>
            <a class="try-editor document" data-action-click="ui.modal.load" data-action-url="
                <?= Url::to([
                    'document',
                    'extension' => 'docx'
                ]); ?>">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    'Document'
                ); ?>
            </a>
        </li>
        <li>
            <a class="try-editor spreadsheet" data-action-click="ui.modal.load" data-action-url="
                <?= Url::to([
                    'document',
                    'extension' => 'xlsx'
                ]); ?>">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    'Spreadsheet'
                ); ?>
            </a>
        </li>
        <li>
            <a class="try-editor presentation" data-action-click="ui.modal.load" data-action-url="
                <?= Url::to([
                    'document',
                    'extension' => 'pptx'
                ]); ?>">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    'Presentation'
                ); ?>
            </a>
        </li>
        <li>
            <a class="try-editor form-template" data-action-click="ui.modal.load" data-action-url="
                <?= Url::to([
                    'document',
                    'extension' => 'pdf'
                ]); ?>">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    'PDF form'
                ); ?>
            </a>
        </li>
    </ul>
</div>
<?php \humhub\widgets\ModalDialog::end(); ?>
