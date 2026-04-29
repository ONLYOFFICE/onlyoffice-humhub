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

namespace humhub\modules\onlyoffice\components;

use Yii;
use yii\web\HttpException;
use yii\helpers\Url;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\Module;
use humhub\components\Controller;
use humhub\modules\onlyoffice\models\Share;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * Description of BaseFileController
 *
 * @author Luke
 */
class BaseFileController extends Controller
{
    /**
     * @var File
     */
    public $file;

    /**
     * @var string the open mode (view, edit)
     */
    public $mode;

    /**
     * @var string the restricted mode
     */
    public $restrict;

    /**
     * @var string the secret used to open this document, if provided
     */
    public $shareSecret;

    /**
     * @inheritdoc
     */
    public function init()
    {

        $shareSecret = Yii::$app->request->get('share', null);
        if (!empty($shareSecret)) {
            $share = Share::findOne(['secret' => $shareSecret]);
            if ($share === null) {
                throw new HttpException(404, Yii::t('OnlyofficeModule.base', 'Could not find shared file!'));
            }

            $this->file = $share->file;
            $this->mode = $share->mode;
            $this->shareSecret = $share->secret;
        } else {
            // Load File
            $this->file = File::findOne(['guid' => Yii::$app->request->get('guid')]);
            if ($this->file === null) {
                throw new HttpException(404, Yii::t('OnlyofficeModule.base', 'Could not find requested file!'));
            }

            if (!$this->file->canRead()) {
                throw new HttpException(403, Yii::t('OnlyofficeModule.base', 'File read access denied!'));
            }

            if (Yii::$app->request->get('restrict') == Module::OPEN_RESTRICT_FILL) {
                $this->restrict = Module::OPEN_RESTRICT_FILL;
            }

            $this->mode = Module::OPEN_MODE_VIEW;

            if (Yii::$app->request->get('mode') == Module::OPEN_MODE_EDIT) {
                if (!$this->file->canDelete() && empty($this->restrict)) {
                    throw new HttpException(403, Yii::t('OnlyofficeModule.base', 'File write access denied!'));
                }
                $this->mode = Module::OPEN_MODE_EDIT;
            }
        }

        parent::init();
    }

    /**
     * Returns the URL for the file content - to redirect to
     *
     * @return string
     */
    protected function determineContentFileUrl()
    {
        // If user is not logged in, use login mask url instead
        if (Yii::$app->user->isGuest) {
            return Yii::$app->user->loginUrl;
        }

        $underlyingObject = $this->file->getPolymorphicRelation();

        if (
            $underlyingObject !== null &&
            $underlyingObject instanceof ContentActiveRecord &&
            $underlyingObject->content->canView()
        ) {

            /** @var ContentActiveRecord $underlyingObject */
            return $underlyingObject->content->getUrl();
        }

        return Url::home();
    }
}
