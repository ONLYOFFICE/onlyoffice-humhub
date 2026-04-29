<?php

/**
 *  Copyright (c) Ascensio System SIA 2026. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\notifications;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Html;
use humhub\modules\notification\components\BaseNotification;
use humhub\components\SocialActivity;

class Mention extends BaseNotification
{
    public $moduleId = "onlyoffice";

    public $viewName = "mentioned";

    public $file;

    public function html()
    {
        return Yii::t(
            'UserModule.notification',
            '{displayName} mentioned you in {contentTitle}.',
            [
                'displayName' => Html::tag('strong', Html::encode($this->originator->displayName)),
                'contentTitle' => $this->source->file->file_name,
            ]
        ) . ' ' .  $this->source->message;
    }

    public function getMailSubject()
    {
        return Yii::t('UserModule.notification', "{displayName} just mentioned you in {contentTitle} \"{preview}\"", [
                    'displayName' => $this->originator->displayName,
                    'contentTitle' => $this->source->file->file_name,
                    'preview' => $this->source->message
        ]);
    }

    public function getViewParams($params = [])
    {
        if ($this->hasContent() && $this->getContent()->updated_at instanceof Expression) {
            $this->getContent()->refresh();
            $date = $this->getContent()->updated_at;
        } elseif ($this->hasContent()) {
            $date = $this->getContent()->updated_at;
        } else {
            $date = null;
        }

        $this->file = $this->source->file;

        $url = Url::to(['/onlyoffice/open',
                'guid' => $this->file->guid,
                'mode' => 'view',
                'seen' => !$this->record->seen,
                'notify' => $this->record->id,
                'anchor' => $this->source->anchor]);

        $result = [
            'url' => $url,
            'relativeUrl' => $url,
            'date' => $date,
            'isNew' => !$this->record->seen,
        ];

        return ArrayHelper::merge(SocialActivity::getViewParams($result), $params);
    }
}
