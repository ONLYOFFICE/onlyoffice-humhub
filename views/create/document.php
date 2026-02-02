<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\helpers\Html;
use humhub\modules\onlyoffice\assets\Assets;
use humhub\widgets\modal\Modal;

Assets::register($this);
?>

<?php $form = Modal::beginFormDialog([
    'title' => Yii::t('OnlyofficeModule.base', '<strong>Create</strong> document'),
    'footer' => Html::submitButton(
        Yii::t('OnlyofficeModule.base', 'Save'),
        ['data-action-click' => 'onlyoffice.createSubmit',
            'data-ui-loader' => '',
            'class' => 'btn btn-primary',
        ],
    ),
]) ?>

    <?= $form->field(
        $model,
        'fileName',
        ['template' => '{label}<div class="input-group">{input}<div class="input-group-text">' .
            $model->extension . '</div></div>{hint}{error}',
        ],
    ) ?>
    <?= $form->field($model, 'openFlag')->checkbox() ?>

    <?= $form->field($model, 'fid')->hiddenInput()->label(false) ?>

<?php Modal::endFormDialog();
