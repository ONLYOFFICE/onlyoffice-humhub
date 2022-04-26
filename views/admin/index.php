<?php

/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\web\View; 
?>

<div class="panel panel-default">

    <div class="panel-heading"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE - Docs</strong> module configuration'); ?></div>

    <div class="panel-body">

        <?php if (empty($model->serverUrl) && empty($trial)): ?>
            <div class="alert alert-warning" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> not configured yet.'); ?></div>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger error" role="alert"><?= Yii::t('OnlyofficeModule.base', 'Error when trying to connect ({error})', ['error' => $error]); ?></div>
        <?php elseif (!empty($version) && !$trial): ?>
            <div class="alert alert-success" role="alert"><?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}', ['version' => $version]); ?></div>
        <?php elseif (!empty($version) && $trial): ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t('OnlyofficeModule.base', '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}', ['version' => $version]); ?> 
                <p style = "color: #84be5e"><?= Yii::t('OnlyofficeModule.base', 'Trial period: {trial} days', ['trial' => $trial]); ?></p>
            </div>
        <?php endif; ?>

        <div class="alert alert-danger invalid-server-url" role="alert" hidden></div>

        <?php $form = ActiveForm::begin(['id' => 'configure-form']); ?>
        <div class="form-group">
            <?= $form->field($model, 'serverUrl'); ?>
            <?= $form->field($model, 'verifyPeerOff')->checkbox(); ?>
            <?= $form->field($model, 'forceSave')->checkbox(); ?>
            <?= $form->field($model, 'demoServer')->checkbox(); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'jwtSecret'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'internalServerUrl'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'storageUrl'); ?>
        </div>

        <div class="form-group">
            <?= Html::activeLabel($model,'customLabel', ['class' => 'control-label']); ?>
            <?= $form->field($model, 'chat')->checkbox(); ?>
            <?= $form->field($model, 'compactHeader')->checkbox(); ?>
            <?= $form->field($model, 'feedback')->checkbox(); ?>
            <?= $form->field($model, 'help')->checkbox(); ?>
            <?= $form->field($model, 'compactToolbar')->checkbox(); ?>
        </div>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary', 'data-ui-loader' => '']) ?>
        </div>

        <?php 
            if (isset($serverApiUrl)) {
                View::registerJsFile($serverApiUrl);
                View::registerJs('
                    if (typeof DocsAPI === "undefined") {
                        if ($(".error").length) {
                            $(".error").append("<p style=\'color: #ff8989\'>' . Yii::t("OnlyofficeModule.base", "<strong>ONLYOFFICE Docs</strong> DocsAPI undefined.") . '</p>");
                        } else {
                            $(".invalid-server-url").html("' . Yii::t("OnlyofficeModule.base", "<strong>ONLYOFFICE Docs</strong> DocsAPI undefined.") . '");
                            $(".invalid-server-url").show();
                        }
                    }
                ');
            }
        ?>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
   if($trial === false) {
    View::registerJs('
        $("#configureform-demoserver").closest("label").css({"cursor":"default", "opacity":"0.5"});
        $("#configureform-demoserver").attr("checked", false);
        $("#configureform-demoserver").attr("disabled", true);
    ');
   }
?>