<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2023. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\controllers;

use Yii;
use yii\helpers\Url;
use humhub\components\access\ControllerAccess;
use humhub\modules\onlyoffice\permissions\CanUseOnlyOffice;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\onlyoffice\Module;

class CreateController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_PERMISSION => [CanUseOnlyOffice::class]],
        ];
    }

    public function actionIndex()
    {
        return $this->renderAjax('index', []);
    }

    public function actionDocument()
    {

        $model = new \humhub\modules\onlyoffice\models\CreateDocument();
        $model->extension = Yii::$app->request->get('extension');

        if ($model->load(Yii::$app->request->post())) {

            $file = $model->save();

            if ($file !== false) {
                return $this->asJson([
                            'success' => true,
                            'file' => FileHelper::getFileInfos($file),
                            'openFlag' => (boolean) $model->openFlag,
                            'openUrl' => Url::to(['/onlyoffice/open', 'guid' => $file->guid, 'mode' => Module::OPEN_MODE_EDIT])
                ]);
            } else {
                return $this->asJson([
                            'success' => false,
                            'output' => $this->renderAjax('document', ['model' => $model])
                ]);
            }
        }

        return $this->renderAjax('document', ['model' => $model]);
    }

    public function determineContentFileUrl($file)
    {
        $underlyingObject = $file->getPolymorphicRelation();

        if (method_exists($underlyingObject, 'getUrl')) {
            return $underlyingObject->getUrl();
        }

        return null;
    }

}
