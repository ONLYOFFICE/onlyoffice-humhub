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
        width: 750px;
    }
    .modal-body {
        display: flex;
        flex-direction: column;
        row-gap: 20px;
        padding: 20px 37px !important;
    }
    .try-descr {
        font-size: 14px;
    }
    .try-editor-list {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .try-editor-list li {
        float: left;
        cursor: pointer;
        border:1px solid #EEE;
        height: 150px;
        padding: 16px;
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
        padding-top: 86px;
        text-align: center;
        text-decoration: none;
    }    
    .try-editor.document {
        margin-top: 14px;
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_docx.svg'); ?>");
    }
    .try-editor.spreadsheet {
        margin-top: 14px;
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_xlsx.svg'); ?>");
    }
    .try-editor.presentation {
        margin-top: 14px;
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_pptx.svg'); ?>");
    }
    .try-editor.form-template {
        margin-top: 14px;
        background-image: url("<?= $this->context->module->getPublishedUrl('/file_pdf.svg'); ?>");
    }
    .try-user-voice {
        font-size: 12px;
        color: #80848F;
    }
    .try-user-voice a {
        color: #80848F;
        text-decoration: underline;
    }
</style>
<div class="modal-body">
    <span class="try-descr"><?= Yii::t(
        'OnlyofficeModule.base',
        'Please select a document type.'
    ); ?></span>
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
    <p class="try-user-voice" >
        <?= Yii::t(
            'OnlyofficeModule.base',
            'Help us improve ONLYOFFICE connector - <a href="{url}" target="_blank">Share feedback</a>',
            [
                'url' => 'https://feedback.onlyoffice.com/forums/966080-your-voice-matters?category_id=519288'
            ]
        ); ?>
    </p>
</div>
<?php \humhub\widgets\ModalDialog::end(); ?>
