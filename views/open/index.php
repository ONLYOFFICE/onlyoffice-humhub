<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\components\View;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\widgets\EditorWidget;
use humhub\widgets\modal\Modal;

/**
 * @var $file File
 * @var $this View
 */

if (!empty($serverApiUrl)) {
    $this->registerJsFile($serverApiUrl, [
        'position' => \yii\web\View::POS_HEAD,
    ]);
}
if (!$openInNewTab) {
    // Force modal full height
    $this->registerCss('#onlyoffice-modal .modal-content {height: calc(100vh - 90px);'
        . ' background-color:transparent; box-shadow: none;}'
        . ' #onlyoffice-modal .modal-dialog {max-width: 98%;}');
} else {
    // Force modal full screen
    $this->registerCss('#onlyoffice-editor-modal {width: 100% !important;'
        . ' margin: 0; z-index: 1050; position: absolute; top: 0;}'
        . '#onlyoffice-editor-modal .modal-content {height: 100vh;');
}
?>

<?php Modal::beginDialog([
    'id' => 'onlyoffice-editor-modal',
    'size' => $openInNewTab ? Modal::SIZE_FULL_SCREEN : Modal::SIZE_EXTRA_LARGE,
    'closeButton' => false,
]) ?>
    <?= EditorWidget::widget([
        'file' => $file,
        'mode' => $mode,
        'restrict' => $restrict,
        'anchor' => $anchor,
        'openInNewTab' => $openInNewTab,
    ]) ?>
<?php Modal::endDialog();
