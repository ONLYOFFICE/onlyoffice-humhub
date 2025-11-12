<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

?>

<div class="panel panel-default">

    <div class="panel-heading">
        <?= Yii::t(
            'OnlyofficeModule.base',
            '<strong>ONLYOFFICE - Docs</strong> module configuration'
        ); ?>
    </div>

    <div class="panel-body">

        <?php if (empty($model->serverUrl) && empty($trial)) : ?>
            <div class="alert alert-warning" role="alert">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    '<strong>ONLYOFFICE Docs</strong> not configured yet.'
                ); ?>
            </div>
        <?php elseif (!empty($model->settingError)) : ?>
            <div class="alert alert-danger error" role="alert">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    'Error when trying to connect ({error})',
                    ['error' => $model->settingError]
                ); ?>
            </div>
        <?php elseif (!empty($model->instaledVersion) && !$trial) : ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}',
                    ['version' => $model->instaledVersion]
                ); ?>
            </div>
        <?php elseif (!empty($model->instaledVersion) && $trial) : ?>
            <div class="alert alert-success" role="alert">
                <?= Yii::t(
                    'OnlyofficeModule.base',
                    '<strong>ONLYOFFICE Docs</strong> successfully connected! - Installed version: {version}',
                    ['version' => $model->instaledVersion]
                ); ?> 
                <p style = "color: #84be5e">
                    <?= Yii::t(
                        'OnlyofficeModule.base',
                        'Trial period: {trial} days',
                        ['trial' => $trial]
                    ); ?>
                </p>
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
            <?= $form->field($model, 'jwtHeader'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'internalServerUrl'); ?>
        </div>

        <div class="form-group">
            <?= $form->field($model, 'storageUrl'); ?>
        </div>

        <div class="form-group">
            <?= Html::activeLabel($model, 'customLabel', ['class' => 'control-label']); ?>
            <?= $form->field($model, 'chat')->checkbox(); ?>
            <?= $form->field($model, 'compactHeader')->checkbox(); ?>
            <?= $form->field($model, 'feedback')->checkbox(); ?>
            <?= $form->field($model, 'help')->checkbox(); ?>
            <?= $form->field($model, 'compactToolbar')->checkbox(); ?>
        </div>

        <div id="forceEditTypes" class="form-group">
            <?= Html::activeLabel($model, 'editLabel', ['class' => 'control-label']); ?>
            <div class="row">
                <?php foreach ($forceEditExt as $key => $ext) {
                    echo '<div class="col-md-2 col-sm-3 col-xs-4">';
                    echo $form->field(
                        $model,
                        'forceEditTypes[' . $ext . ']',
                        ['options' => ['class' => 'checkbox']]
                    )->checkbox(['label' => $ext]);
                    echo '</div>';
                } ?>
            </div>
        </div>

        <div class="form-group">
            <?= Html::Button(
                Yii::t(
                    'base',
                    'Submit'
                ),
                ['id' => 'saveBtn', 'class' => 'btn btn-primary', 'data-ui-loader' => '']
            ) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>
<?php
    View::registerJs('
        humhub.module("onlyoffice", function (module, require, $) {
            var serverApiUrl = "' . $serverApiUrl . '";
            var trial = "' . json_encode($trial) . '";

            var testApiResult = function(){
                if (typeof DocsAPI === "undefined") {
                    if ($(".error").length) {
                        $(".error").append("<p style=\'color: #ff8989\'>' .
                        Yii::t(
                            "OnlyofficeModule.base",
                            "<strong>ONLYOFFICE Docs</strong> DocsAPI undefined."
                        ) . '</p>");
                    } else {
                        $(".invalid-server-url").html("' .
                        Yii::t(
                            "OnlyofficeModule.base",
                            "<strong>ONLYOFFICE Docs</strong> DocsAPI undefined."
                        ) . '");
                        $(".invalid-server-url").show();
                    }
                }
                delete DocsAPI;
            }

            if (trial == "false") {
                $("#configureform-demoserver").closest("label").css({"cursor":"default", "opacity":"0.5"});
                $("#configureform-demoserver").attr("checked", false);
                $("#configureform-demoserver").attr("disabled", true);
                $("#configureform-demoserver").closest("div").children()[2].innerText = "' .
                Yii::t(
                    "OnlyofficeModule.base",
                    "The 30-day test period is over, you can no longer connect to demo ONLYOFFICE Docs server."
                ) . '";
            }

            if (serverApiUrl.length > 0) {
                var js = document.createElement("script");
                js.setAttribute("type", "text/javascript");
                js.setAttribute("id", "scripDocServiceAddress");
                document.getElementsByTagName("head")[0].appendChild(js);

                var scriptAddress = $("#scripDocServiceAddress");

                scriptAddress.on("load", testApiResult).on("error", testApiResult);
                scriptAddress.attr("src", serverApiUrl);
            }

            $("#saveBtn").click(function(evt) {
                var saveBtnClone = $("#saveBtn").clone(true, false);

                var serverUrl = $("#configureform-serverurl").val();
                var verifyPeerOff = $("#configureform-verifypeeroff").prop("checked") ? 1 : 0;
                var forceSave = $("#configureform-forcesave").prop("checked") ? 1 : 0;
                var demoServer = $("#configureform-demoserver").prop("checked") ? 1 : 0;

                var jwtSecret = $("#configureform-jwtsecret").val();
                var jwtHeader = $("#configureform-jwtheader").val();
                var internalServerUrl = $("#configureform-internalserverurl").val();
                var storageUrl = $("#configureform-storageurl").val();

                var chat = $("#configureform-chat").prop("checked") ? 1 : 0;
                var compactHeader = $("#configureform-compactheader").prop("checked") ? 1 : 0;
                var feedback = $("#configureform-feedback").prop("checked") ? 1 : 0;
                var help = $("#configureform-help").prop("checked") ? 1 : 0;
                var compactToolbar = $("#configureform-compacttoolbar").prop("checked") ? 1 : 0;

                var forceEditTypes = {};
                var forceEditTypesNodes = $("#forceEditTypes").find("input[type=checkbox]");
                $.each(forceEditTypesNodes, function(i, node){
                    var type = $(node).attr("id").replace("configureform-forceedittypes-", "");
                    forceEditTypes[type] = $(node).prop("checked") ? 1 : 0;
                });

                $.ajax({
                    url: "' . Url::to(["/onlyoffice/admin/save"]) . '",
                    cache: false,
                    type: "POST",
                    data: { 
                        "ConfigureForm": {
                            serverUrl: serverUrl,
                            verifyPeerOff: verifyPeerOff,
                            forceSave: forceSave,
                            demoServer: demoServer,
                            jwtSecret: jwtSecret,
                            jwtHeader: jwtHeader,
                            internalServerUrl: internalServerUrl,
                            storageUrl: storageUrl,
                            chat: chat,
                            compactHeader: compactHeader,
                            feedback: feedback,
                            help: help,
                            compactToolbar: compactToolbar,
                            forceEditTypes: forceEditTypes
                        }
                    },
                    dataType: "json"
                }).catch(function (e) {
                    $("#saveBtn").replaceWith(saveBtnClone);
                    module.log.error(e, true);
                })
            });
        });
    '); ?>
