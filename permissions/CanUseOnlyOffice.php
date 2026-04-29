<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\permissions;

use Yii;
use humhub\libs\BasePermission;

/**
 * CanUseOnlyOffice Permissions
 */
class CanUseOnlyOffice extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $moduleId = 'onlyoffice';

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return Yii::t('OnlyofficeModule.base', 'Can use ONLYOFFICE');
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return Yii::t('OnlyofficeModule.base', 'Allows the user to use ONLYOFFICE.');
    }

    /**
     * @inheritdoc
     */
    protected $defaultState = self::STATE_ALLOW;
}
