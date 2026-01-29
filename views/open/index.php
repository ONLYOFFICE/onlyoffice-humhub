<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
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
// Force modal full height
$this->registerCss('#onlyoffice-modal .modal-content {height: calc(100vh - 90px);'
    . ' background-color:transparent; box-shadow: none;}'
    . ' #onlyoffice-modal .modal-dialog {max-width: 98%;}');
?>

<?php Modal::beginDialog([
    'size' => Modal::SIZE_FULL_SCREEN,
    'closeButton' => false,
]) ?>
    <?= EditorWidget::widget([
        'file' => $file,
        'mode' => $mode,
        'restrict' => $restrict,
        'anchor' => $anchor,
    ]) ?>
<?php Modal::endDialog();
