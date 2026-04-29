<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\onlyoffice\Module;
use humhub\modules\onlyoffice\models\Share;
use humhub\modules\onlyoffice\components\BaseFileController;

class ShareController extends BaseFileController
{
    public function init()
    {
        parent::init();

        if ($this->mode !== Module::OPEN_MODE_EDIT) {
            throw new HttpException('400', 'Could not share when in edit mode!');
        }
    }

    /**
     * Share Modal
     *
     * @return type
     * @throws HttpException
     */
    public function actionIndex()
    {
        return $this->renderAjax('share', ['file' => $this->file, 'mode' => $this->mode]);
    }

    public function actionRemove()
    {
        Yii::$app->response->format = 'json';
        Share::deleteAll(['file_id' => $this->file->id, 'mode' => Yii::$app->request->post('shareMode')]);
        return [
            'success' => true
        ];
    }

    public function actionGet()
    {
        Yii::$app->response->format = 'json';
        $url = Share::getShareLink($this->file, true, Yii::$app->request->post('shareMode'));
        return [
            'url' => $url
        ];
    }
}
