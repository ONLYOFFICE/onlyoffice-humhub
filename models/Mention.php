<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\models;

use humhub\modules\file\models\File;

/**
 * This is the model class for table "onlyoffice_mention".
 *
 * @property integer $id
 * @property integer $file_id
 * @property string $message
 * @property string $anchor
 *
 * @property File $file
 */
class Mention extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'onlyoffice_mention';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(File::className(), ['id' => 'file_id']);
    }

    public static function generateMention($file, $message, $anchor)
    {
        if (strlen($message) > 255) {
            $message = mb_strimwidth($message, 0, 255, "...");
        }
        $mention = new self();
        $mention->file_id = $file->id;
        $mention->message = $message;
        $mention->anchor = $anchor;
        $mention->save();

        return $mention;
    }
}
