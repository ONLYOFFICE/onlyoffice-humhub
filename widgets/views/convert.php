<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\libs\Html;
use humhub\widgets\ModalDialog;
use yii\web\View;

\humhub\modules\onlyoffice\assets\Assets::register($this);
?>

<?php $modal = ModalDialog::begin(['header' => Yii::t('OnlyofficeModule.base', '<strong>Convert</strong> document')]) ?>
<?= Html::beginTag('div', $options) ?>

<div class="modal-body">
    <span>
        <?= Yii::t(
            'OnlyofficeModule.base',
            'Converting <strong>{oldFileName}</strong> to <strong>{newFileName}</strong>..',
            ['oldFileName' => $file->fileName, 'newFileName' => $newName]
        ); ?>
    </span>
    <br/>
    <span id="oConvertMessage"></span>

</div>

<div class="modal-footer">
    <a href="#" data-modal-close class="btn btn-primary" data-action-click="close" data-ui-loader>
        <?= Yii::t('OnlyofficeModule.base', 'Close'); ?>
    </a>
</div>

<?= Html::endTag('div'); ?>
<?php ModalDialog::end(); ?>
<?php
    View::registerJs('
        $(document).on("click", ".modal-header .close", function (e) {
            var $convertWidget = $("[data-ui-widget=\"onlyoffice.Convert\"]");
            var widget = $convertWidget.data("ui-widget");

            if (widget) {
                if (typeof widget.close === "function") {
                    widget.close({});
                } else {
                    $convertWidget.find("[data-action-click=\"close\"]").trigger("click");
                }
            }
        });
    '); ?>