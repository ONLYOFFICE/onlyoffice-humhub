<?php

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice\models;

use Yii;

/**
 * ConfigureForm defines the configurable fields.
 */
class ConfigureForm extends \yii\base\Model
{
    public $serverUrl;
    public $verifyPeerOff;
    public $jwtSecret;
    public $jwtHeader;
    public $internalServerUrl;
    public $storageUrl;
    public $demoServer;

    public $chat;
    public $compactHeader;
    public $feedback;
    public $help;
    public $compactToolbar;
    public $toolbarNoTabs;
    public $customLabel;
    public $forceSave;

    public $forceEditTypes;

    public $settingError;
    public $instaledVersion;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['serverUrl', 'string'],
            ['verifyPeerOff', 'boolean'],
            ['jwtSecret', 'string'],
            ['jwtHeader', 'string'],
            ['internalServerUrl', 'string'],
            ['storageUrl', 'string'],
            ['demoServer', 'boolean'],
            ['chat', 'boolean'],
            ['compactHeader', 'boolean'],
            ['feedback', 'boolean'],
            ['help', 'boolean'],
            ['compactToolbar', 'boolean'],
            ['toolbarNoTabs', 'boolean'],
            ['forceSave', 'boolean'],
            ['forceEditTypes', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'serverUrl' => Yii::t(
                'OnlyofficeModule.base',
                'Hostname'
            ),
            'verifyPeerOff' => Yii::t(
                'OnlyofficeModule.base',
                'Disable certificate verification (insecure)'
            ),
            'jwtSecret' => Yii::t(
                'OnlyofficeModule.base',
                'JWT Secret'
            ),
            'jwtHeader' => Yii::t(
                'OnlyofficeModule.base',
                'Authorization header'
            ),
            'internalServerUrl' => Yii::t(
                'OnlyofficeModule.base',
                'ONLYOFFICE Docs address for internal requests from the server'
            ),
            'storageUrl' => Yii::t(
                'OnlyofficeModule.base',
                'Server address for internal requests from ONLYOFFICE Docs'
            ),
            'demoServer' => Yii::t(
                'OnlyofficeModule.base',
                'Connect to demo ONLYOFFICE Docs server'
            ),
            'chat' => Yii::t(
                'OnlyofficeModule.base',
                'Display Chat menu button'
            ),
            'compactHeader' => Yii::t(
                'OnlyofficeModule.base',
                'Display the header more compact'
            ),
            'feedback' => Yii::t(
                'OnlyofficeModule.base',
                'Display Feedback & Support menu button'
            ),
            'help' => Yii::t(
                'OnlyofficeModule.base',
                'Display Help menu button'
            ),
            'compactToolbar' => Yii::t(
                'OnlyofficeModule.base',
                'Display the toolbar more compact'
            ),
            'toolbarNoTabs' => Yii::t(
                'OnlyofficeModule.base',
                'Display monochrome toolbar header'
            ),
            'customLabel' => Yii::t(
                'OnlyofficeModule.base',
                'The customization section allows personalizing the editor interface'
            ),
            'forceSave' => Yii::t(
                'OnlyofficeModule.base',
                'Keep intermediate versions when editing (forcesave)'
            ),
            'editLabel' => Yii::t(
                'OnlyofficeModule.base',
                'Open the file for editing (due to format restrictions, the data might ' .
                'be lost when saving to the formats from the list below)'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'serverUrl' => Yii::t(
                'OnlyofficeModule.base',
                'e.g. http://documentserver'
            ),
            'jwtSecret' => Yii::t(
                'OnlyofficeModule.base',
                'JWT Secret key (leave blank to disable)'
            ),
            'jwtHeader' => Yii::t(
                'OnlyofficeModule.base',
                'Leave blank to use default header'
            ),
            'internalServerUrl' => Yii::t(
                'OnlyofficeModule.base',
                'e.g. http://documentserver'
            ),
            'storageUrl' => Yii::t(
                'OnlyofficeModule.base',
                'e.g. http://storage'
            ),
            'demoServer' => Yii::t(
                'OnlyofficeModule.base',
                'This is a public test server, please do not use it for private sensitive data. ' .
                'The server will be available during a 30-day period.'
            ),
        ];
    }

    public function loadSettings()
    {
        $this->serverUrl = Yii::$app->getModule('onlyoffice')->settings->get('serverUrl');
        $this->verifyPeerOff = (bool)Yii::$app->getModule('onlyoffice')->settings->get('verifyPeerOff');
        $this->jwtSecret = Yii::$app->getModule('onlyoffice')->settings->get('jwtSecret');
        $this->jwtHeader = Yii::$app->getModule('onlyoffice')->settings->get('jwtHeader');
        $this->internalServerUrl = Yii::$app->getModule('onlyoffice')->settings->get('internalServerUrl');
        $this->storageUrl = Yii::$app->getModule('onlyoffice')->settings->get('storageUrl');
        $this->demoServer = (bool)Yii::$app->getModule('onlyoffice')->settings->get('demoServer');
        $this->chat = (bool)Yii::$app->getModule('onlyoffice')->settings->get('chat');
        $this->compactHeader = (bool)Yii::$app->getModule('onlyoffice')->settings->get('compactHeader');
        $this->feedback = (bool)Yii::$app->getModule('onlyoffice')->settings->get('feedback');
        $this->help = (bool)Yii::$app->getModule('onlyoffice')->settings->get('help');
        $this->compactToolbar = (bool)Yii::$app->getModule('onlyoffice')->settings->get('compactToolbar');
        $this->toolbarNoTabs = (bool)Yii::$app->getModule('onlyoffice')->settings->get('toolbarNoTabs');
        $this->forceSave = (bool)Yii::$app->getModule('onlyoffice')->settings->get('forceSave');
        $this->forceEditTypes = $this->deserializeForceEditTypes();

        $this->settingError = Yii::$app->getModule('onlyoffice')->settings->get('settingError');
        $this->instaledVersion = Yii::$app->getModule('onlyoffice')->settings->get('instaledVersion');

        return true;
    }

    public function save()
    {
        Yii::$app->getModule('onlyoffice')->settings->set('serverUrl', rtrim($this->serverUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('verifyPeerOff', $this->verifyPeerOff);
        Yii::$app->getModule('onlyoffice')->settings->set('jwtSecret', $this->jwtSecret);
        Yii::$app->getModule('onlyoffice')->settings->set('jwtHeader', $this->jwtHeader);
        Yii::$app->getModule('onlyoffice')->settings->set('internalServerUrl', rtrim($this->internalServerUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('storageUrl', rtrim($this->storageUrl, '/'));
        Yii::$app->getModule('onlyoffice')->settings->set('demoServer', $this->demoServer);
        Yii::$app->getModule('onlyoffice')->settings->set('chat', $this->chat);
        Yii::$app->getModule('onlyoffice')->settings->set('compactHeader', $this->compactHeader);
        Yii::$app->getModule('onlyoffice')->settings->set('feedback', $this->feedback);
        Yii::$app->getModule('onlyoffice')->settings->set('help', $this->help);
        Yii::$app->getModule('onlyoffice')->settings->set('compactToolbar', $this->compactToolbar);
        Yii::$app->getModule('onlyoffice')->settings->set('toolbarNoTabs', $this->toolbarNoTabs);
        Yii::$app->getModule('onlyoffice')->settings->set('forceSave', $this->forceSave);
        Yii::$app->getModule('onlyoffice')->settings->set("forceEditTypes", $this->serializeForceEditTypes());

        return true;
    }

    public function saveConnectionInfo()
    {
        Yii::$app->getModule('onlyoffice')->settings->set('settingError', $this->settingError);
        Yii::$app->getModule('onlyoffice')->settings->set('instaledVersion', $this->instaledVersion);
    }

    public function deserializeForceEditTypes()
    {
        $result = [];
        foreach (explode(",", Yii::$app->getModule('onlyoffice')->settings->get("forceEditTypes") ?? "") as $ext) {
            $result[$ext] = 1;
        }
        return $result;
    }

    public function serializeForceEditTypes()
    {
        return implode(",", array_keys(array_filter($this->forceEditTypes, function ($ext) {
            return $ext == true;
        })));
    }
}
