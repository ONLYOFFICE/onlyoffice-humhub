<?php

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

use humhub\libs\Html;

?>
<div class="modal-dialog animated fadeIn" style="width:96%">
    <div class="modal-content onlyofficeModal" style="background-color:transparent;">
        <?=
        \humhub\modules\onlyoffice\widgets\EditorWidget::widget([
            'file' => $file,
            'mode' => $mode,
            'anchor' => $anchor
        ]);
        ?>
    </div>
</div>
<script <?= Html::nonce() ?>>
    window.onload = function (evt) {
        setSize();
    }
    window.onresize = function (evt) {
        setSize();
    }
    setSize();

    function setSize() {
        $('.onlyofficeModal').css('height', window.innerHeight - 110 + 'px');
    }
</script>
