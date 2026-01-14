<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\helpers\Html;
use humhub\modules\onlyoffice\assets\Assets;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

Assets::register($this);
?>

<?php Modal::beginDialog([
    'title' => Yii::t('OnlyofficeModule.base', '<strong>Convert</strong> document'),
    'footer' => Html::a(
        Yii::t('OnlyofficeModule.base', 'Close'),
        '#',
        [
                'class' => 'btn btn btn-light',
                'data-ui-loader' => '',
                'data-action-click' => 'close',
                'data-modal-close' => '',
                'data-ui-widget' => 'onlyoffice.Convert',
            ],
    ),
]) ?>

    <?= Html::beginTag('div', $options) ?>

        <span>
            <?= Yii::t(
                'OnlyofficeModule.base',
                'Converting <strong>{oldFileName}</strong> to <strong>{newFileName}</strong>..',
                ['oldFileName' => $file->fileName, 'newFileName' => $newName]
            ); ?>
        </span>
        <br/>
        <span id="oConvertMessage"></span>

    <?= Html::endTag('div'); ?>

<?php Modal::endDialog() ?>
