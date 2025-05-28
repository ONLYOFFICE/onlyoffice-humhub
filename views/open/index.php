<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\web\View;

?>

<div class="modal-dialog animated fadeIn" style="width:96%">
    <div class="modal-content onlyofficeModal" style="background-color:transparent;">
        <?=
        \humhub\modules\onlyoffice\widgets\EditorWidget::widget([
            'file' => $file,
            'mode' => $mode,
            'restrict' => $restrict,
            'anchor' => $anchor
        ]);
        ?>
    </div>
</div>

<?php
    if (!empty($serverApiUrl)) {
        $this->registerJsFile($serverApiUrl, [
            'position' => \yii\web\View::POS_HEAD,
        ]);
    }
    View::registerJs('
        window.onload = function (evt) {
            setSize();
        };
        window.onresize = function (evt) {
            setSize();
        };
        setSize();

        function setSize() {
            $(".onlyofficeModal").css("height", window.innerHeight - 110 + "px");
        }
    '); ?>
