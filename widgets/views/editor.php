<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\helpers\Url;
use humhub\libs\Html;
use humhub\modules\onlyoffice\Module;

\humhub\modules\onlyoffice\assets\Assets::register($this);


$headerBackgroundColor = '';

if ($documentType === Module::DOCUMENT_TYPE_SPREADSHEET) {
    $headerBackgroundColor = '#8CA946';
} elseif ($documentType === Module::DOCUMENT_TYPE_TEXT) {
    $headerBackgroundColor = '#5A7DC9';
} elseif ($documentType === Module::DOCUMENT_TYPE_PRESENTATION) {
    $headerBackgroundColor = '#DD682B';
} elseif ($documentType === Module::DOCUMENT_TYPE_PDF) {
    $headerBackgroundColor = '#D45757';
} elseif ($documentType === Module::DOCUMENT_TYPE_DIAGRAM) {
    $headerBackgroundColor = '#444796';
}
?>

<?= Html::beginTag('div', $options) ?>
<div style="height:50px;
    border-radius: 8px 8px 0px 0px;
    background-color:<?= $headerBackgroundColor; ?>;
    padding-top:7px;
    padding-right:7px">
    <div class = "pull-right">
        <?php if ($mode === Module::OPEN_MODE_EDIT && !Yii::$app->user->isGuest) : ?>
            <?= humhub\libs\Html::a(
                Yii::t('OnlyofficeModule.base', 'Share'),
                '#',
                ['class' => 'btn btn btn-default',
                    'data-action-click' => 'share',
                    'data-action-block' => 'sync',
                    'data-action-url' => Url::to([
                        '/onlyoffice/share',
                        'guid' => $file->guid,
                        'mode' => $mode
                    ])
                ]
            ); ?>
        <?php endif; ?>
        <?= humhub\libs\Html::a(
            Yii::t('OnlyofficeModule.base', 'Close'),
            '#',
            ['class' => 'btn btn btn-default',
                'data-ui-loader' => '',
                'data-action-click' => 'close',
                'data-action-block' => 'manual'
            ]
        ); ?>
    </div>
</div>
<div id="iframeContainer"></div>
<?= Html::endTag('div'); ?>
