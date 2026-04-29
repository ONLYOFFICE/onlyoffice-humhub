<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\models;

use Yii;
use yii\helpers\Url;
use humhub\modules\file\models\File;

/**
 * This is the model class for table "onlyoffice_share".
 *
 * @property integer $id
 * @property integer $file_id
 * @property string $secret
 * @property string $mode $mode
 *
 * @property File $file
 */
class Share extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'onlyoffice_share';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id']);
    }

    public static function getShareLink($file, $generateNew, $mode)
    {
        $share = self::findOne(['file_id' => $file->id, 'mode' => $mode]);
        if ($share === null) {
            if ($generateNew === false) {
                return null;
            }
            $share = self::generateShareLink($file, $mode);
        }

        return Url::to(['/onlyoffice/open', 'share' => $share->secret], true);
    }

    public static function generateShareLink($file, $mode)
    {
        $share = new self();
        $share->file_id = $file->id;
        $share->mode = $mode;
        $share->secret = bin2hex(Yii::$app->security->generateRandomKey(25));
        $share->save();

        return $share;
    }
}
