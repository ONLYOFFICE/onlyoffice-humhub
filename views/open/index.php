<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

use yii\web\View;

?>
<style>
    .onlyoffice-modal-fullscreen {
        width: 100% !important;
        margin: 0;
    }

    .onlyoffice-modal-content-fullscreen {
        height: 100vh;
    }
</style>

<div class="modal-dialog animated fadeIn <?php if ($openInNewTab) :
    ?>onlyoffice-modal-fullscreen<?php
                                         endif; ?>" style="width:96%;">
    <div class="modal-content <?php if ($openInNewTab) :
        ?>onlyoffice-modal-content-fullscreen<?php
                              endif; ?> onlyofficeModal" style="background-color:transparent;">
        <?=
        \humhub\modules\onlyoffice\widgets\EditorWidget::widget([
            'file' => $file,
            'mode' => $mode,
            'restrict' => $restrict,
            'anchor' => $anchor,
            'openInNewTab' => $openInNewTab,
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
if (!$openInNewTab) {
    $this->registerJs('
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
    ');
}; ?>
