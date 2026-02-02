<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 *  Copyright (c) Ascensio System SIA 2024. All rights reserved.
 *  http://www.onlyoffice.com
 */

namespace humhub\modules\onlyoffice;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use humhub\libs\CURLHelper;
use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use stdClass;
use Yii;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\Response;

/**
 * File Module
 *
 * @since 0.5
 */
class Module extends \humhub\components\Module
{
    private $formats;

    public $resourcesPath = 'resources';

    public int $jwtExpiration = 300;

    /**
     * Open modes
     */
    public const OPEN_MODE_VIEW = 'view';
    public const OPEN_MODE_EDIT = 'edit';

    /**
     * Restricted open modes
    */
    public const OPEN_RESTRICT_FILL = 'fill';

    /**
     * Only document types
     */
    public const DOCUMENT_TYPE_TEXT = 'word';
    public const DOCUMENT_TYPE_PRESENTATION = 'slide';
    public const DOCUMENT_TYPE_SPREADSHEET = 'cell';
    public const DOCUMENT_TYPE_PDF = 'pdf';
    public const DOCUMENT_TYPE_DIAGRAM = 'diagram';

    public $demoparam = [
        'trial' => 30,
        'header' => 'AuthorizationJWT',
        'secret' => 'sn2puSUF7muF5Jas',
        'serverUrl' => 'https://onlinedocs.docs.onlyoffice.com'
    ];

    public function formats()
    {
        if (isset($this->formatFields)) {
            return $this->formatFields;
        }

        $formatsJsonContent = file_get_contents($this->getAssetPath() . '/formats/onlyoffice-docs-formats.json');
        $formatsJson = json_decode($formatsJsonContent);

        $this->formats = new stdClass();
        $this->formats->spreadsheetExtensions = [];
        $this->formats->presentationExtensions = [];
        $this->formats->textExtensions = [];
        $this->formats->pdfExtensions = [];
        $this->formats->diagramExtensions = [];
        $this->formats->editableExtensions = [];
        $this->formats->convertableExtensions = [];
        $this->formats->forceEditableExtensions = [];
        $this->formats->convertsTo = [];
        $this->formats->mimes = [];

        foreach ($formatsJson as $formatJson) {
            if ($formatJson->type === self::DOCUMENT_TYPE_SPREADSHEET) {
                array_push($this->formats->spreadsheetExtensions, $formatJson->name);
            }
            if ($formatJson->type === self::DOCUMENT_TYPE_PRESENTATION) {
                array_push($this->formats->presentationExtensions, $formatJson->name);
            }
            if ($formatJson->type === self::DOCUMENT_TYPE_TEXT) {
                array_push($this->formats->textExtensions, $formatJson->name);
            }
            if ($formatJson->type === self::DOCUMENT_TYPE_PDF) {
                array_push($this->formats->pdfExtensions, $formatJson->name);
            }
            if ($formatJson->type === self::DOCUMENT_TYPE_DIAGRAM) {
                array_push($this->formats->diagramExtensions, $formatJson->name);
            }

            if (in_array('edit', $formatJson->actions)) {
                array_push($this->formats->editableExtensions, $formatJson->name);
            }

            if (in_array('auto-convert', $formatJson->actions)) {
                array_push($this->formats->convertableExtensions, $formatJson->name);
                $this->formats->convertsTo[$formatJson->name] = $formatJson->convert[0];
            }

            if (in_array('lossy-edit', $formatJson->actions)) {
                array_push($this->formats->forceEditableExtensions, $formatJson->name);
            }

            $this->formats->mimes[$formatJson->name] = count($formatJson->mime) > 0
                ? $formatJson->mime[0]
                : 'application/octet-stream';
        }

        return $this->formats;
    }

    public function isJwtEnabled()
    {
        if ($this->isDemoServerEnabled()) {
            return true;
        }

        return !empty($this->getJwtSecret());
    }

    public function getJwtSecret()
    {
        if ($this->isDemoServerEnabled()) {
            return $this->demoparam['secret'];
        }

        return $this->settings->get('jwtSecret');
    }

    public function getJwtAlgorithm(): string
    {
        return 'HS256';
    }

    public function jwtEncode(array $data): string
    {
        $cryptData = null;

        $data['exp'] = time() + $this->jwtExpiration;

        if (class_exists(Key::class)) {
            $cryptData = JWT::encode($data, $this->getJwtSecret(), $this->getJwtAlgorithm());
        } else {
            $cryptData = JWT::encode($data, $this->getJwtSecret());
        }

        return $cryptData;
    }

    public function jwtDecode(string $hash): stdClass
    {
        $data = new stdClass();

        if (class_exists(Key::class)) {
            $data = JWT::decode($hash, new Key($this->getJwtSecret(), $this->getJwtAlgorithm()));
        } else {
            $data = JWT::decode($hash, $this->getJwtSecret(), array($this->getJwtAlgorithm()));
        }

        return $data;
    }

    public function getServerUrl()
    {
        if ($this->isDemoServerEnabled()) {
            return $this->demoparam['serverUrl'];
        }

        return $this->settings->get('serverUrl');
    }

    public function getInternalServerUrl()
    {
        $url = $this->settings->get('internalServerUrl');

        if (empty($url)) {
            $url = $this->getServerUrl();
        }

        return $url;
    }

    public function getStorageUrl()
    {
        return $this->settings->get('storageUrl');
    }

    public function getVerifyPeerOff()
    {
        return $this->settings->get('verifyPeerOff');
    }

    public function getDemoServer()
    {
        return $this->settings->get('demoServer');
    }

    public function isDemoServerEnabled()
    {
        if (boolval($this->getDemoServer())) {
            $trial = $this->getTrial();

            if ($trial !== false) {
                return true;
            }
        }

        return false;
    }

    public function getChat()
    {
        return boolval($this->settings->get('chat'));
    }

    public function getCompactHeader()
    {
        return boolval($this->settings->get('compactHeader'));
    }

    public function getFeedback()
    {
        return boolval($this->settings->get('feedback'));
    }

    public function getHelp()
    {
        return boolval($this->settings->get('help'));
    }

    public function getCompactToolbar()
    {
        return boolval($this->settings->get('compactToolbar'));
    }

    public function getForceSave()
    {
        return boolval($this->settings->get('forceSave'));
    }

    public function getforceEditTypes()
    {
        $forceEditTypes = $this->settings->get('forceEditTypes');
        return $forceEditTypes === null || $forceEditTypes === '' ? [] : explode(',', $forceEditTypes);
    }

    public function getServerApiUrl(): string
    {
        return $this->getServerUrl() . '/web-apps/apps/api/documents/api.js';
    }

    public function getHeader(): string
    {
        $header = $this->settings->get('jwtHeader');
        if ($this->isDemoServerEnabled()) {
            $header = $this->demoparam['header'];
        }

        return !empty($header) ? $header : 'Authorization';
    }

    public function getTrial()
    {
        $settings =  Yii::$app->getModule('onlyoffice')->settings;
        $trial = $settings->get('trial');

        if (empty($trial)) {
            $settings->set('trial', time());
        } elseif ($this->demoparam['trial'] - round((time() - $trial) / (60 * 60 * 24)) < 0) {
            return false;
        }

        return $this->demoparam['trial'] - round((time() - $trial) / (60 * 60 * 24));
    }

    public function getDocumentType($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));

        if (in_array($fileExtension, $this->formats()->spreadsheetExtensions)) {
            return self::DOCUMENT_TYPE_SPREADSHEET;
        } elseif (in_array($fileExtension, $this->formats()->presentationExtensions)) {
            return self::DOCUMENT_TYPE_PRESENTATION;
        } elseif (in_array($fileExtension, $this->formats()->textExtensions)) {
            return self::DOCUMENT_TYPE_TEXT;
        } elseif (in_array($fileExtension, $this->formats()->pdfExtensions)) {
            return self::DOCUMENT_TYPE_PDF;
        } elseif (in_array($fileExtension, $this->formats()->diagramExtensions)) {
            return self::DOCUMENT_TYPE_DIAGRAM;
        }

        return null;
    }

    public function canEdit($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));
        return in_array($fileExtension, array_merge($this->getforceEditTypes(), $this->formats()->editableExtensions));
    }

    public function canConvert($file)
    {
        $fileExtension = strtolower(FileHelper::getExtension($file));
        return in_array($fileExtension, $this->formats()->convertableExtensions);
    }

    public function canView($file)
    {
        return !empty($this->getDocumentType($file));
    }

    public function getConfigUrl()
    {
        return Url::to([
            '/onlyoffice/admin'
        ]);
    }

    /**
     * Generate unique document key
     *
     * @return string
     */
    public function generateDocumentKey($file)
    {
        if (!empty($file->onlyoffice_key)) {
            return $file->onlyoffice_key;
        }

        $key = substr(strtolower(md5(Yii::$app->security->generateRandomString(20))), 0, 20);
        $file->updateAttributes(['onlyoffice_key' => $key]);

        return $key;
    }

    public function commandService($data): array
    {
        $url = $this->getInternalServerUrl() . '/command';

        try {
            $headers = [];
            $headers['Accept'] = 'application/json';
            if ($this->isJwtEnabled()) {
                $data['token'] = $this->jwtEncode($data);
                $headers[$this->getHeader()] = 'Bearer ' . $this->jwtEncode(['payload' => $data]);
            }

            $options = array(
                'headers' => $headers,
                'body' => $data
            );

            $response = $this->request($url, 'POST', $options)->getData();
            if (isset($response['error'])) {
                $this->commandResponceError($response['error']);
            }

            return $response;
        } catch (\Exception $ex) {
            Yii::error('CommandService: ' . $ex->getMessage());
            return ['error' => $ex->getMessage()];
        }
    }

    public function fileToConversion($file, $ts, $toExt = null, $async = true)
    {
        $key = $this->generateDocumentKey($file);

        $user = Yii::$app->user->getIdentity();
        $userGuid = null;
        if (isset($user->guid)) {
            $userGuid = $user->guid;
        }

        $docHash = $this->generateHash($key, $userGuid);

        $fromExt = strtolower(FileHelper::getExtension($file));

        $downloadUrl = Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], true);
        if (!empty($this->getStorageUrl())) {
            $downloadUrl = $this->getStorageUrl() . Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], false);
        }

        if (is_null($toExt)) {
            $toExt = $this->formats()->convertsTo[$fromExt];
        }

        return $this->convertService($downloadUrl, $fromExt, $toExt, $key . $ts, $async);
    }

    public function convertService($documentUrl, $fromExt, $toExt, $key, $async = true): array
    {
        $url = $this->getInternalServerUrl() . '/converter' . '?shardKey=' . $key;

        $user = Yii::$app->user->getIdentity();
        $lang = ($user) && !empty($user->language) ? $user->language : Yii::$app->language;
        if (!array_key_exists($lang, $this->languageCodes)) {
            $region = 'en-US';
        } else {
            $region = $this->languageCodes[$lang];
        }

        $data = [
            'async' => $async,
            'embeddedfonts' => true,
            'filetype' => $fromExt,
            'outputtype' => $toExt,
            'key' => $key,
            'url' => $documentUrl,
            'region' => $region
        ];

        try {
            $headers = [];
            $headers['Accept'] = 'application/json';
            if ($this->isJwtEnabled()) {
                $data['token'] = $this->jwtEncode($data);
                $headers[$this->getHeader()] = 'Bearer ' . $this->jwtEncode(['payload' => $data]);
            }

            $options = [
                'headers' => $headers,
                'body' => $data
            ];

            $response = $this->request($url, 'POST', $options)->getData();
            if (isset($response['error'])) {
                $this->convertResponceError($response['error']);
            }

            return $response;
        } catch (\Exception $ex) {
            Yii::error('ConvertService: ' . $ex->getMessage());
            return ['error' => $ex->getMessage()];
        }
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if (!$contentContainer) {
            return [
                new permissions\CanUseOnlyOffice(),
            ];
        }

        return [];
    }

    /**
     * @inheritdoc
     */
    public function generateHash($key, $userGuid, $isEmpty = false)
    {
        $data = [];

        if (!empty($key)) {
            $data['key'] = $key;
        }

        if (!empty($userGuid)) {
            $data['userGuid'] = $userGuid;
        }

        if ($isEmpty) {
            $data['isEmpty'] = true;
        }

        $cryptData = null;
        if (class_exists(Key::class)) {
            $cryptData = JWT::encode($data, Yii::$app->settings->get('secret'), $this->getJwtAlgorithm());
        } else {
            $cryptData = JWT::encode($data, Yii::$app->settings->get('secret'));
        }

        return $cryptData;
    }

    /**
     * @inheritdoc
     */
    public function readHash($hash)
    {
        $data = null;
        try {
            if (class_exists(Key::class)) {
                $data = JWT::decode($hash, new Key(Yii::$app->settings->get('secret'), $this->getJwtAlgorithm()));
            } else {
                $data = JWT::decode($hash, Yii::$app->settings->get('secret'), array($this->getJwtAlgorithm()));
            }
        } catch (\Exception $ex) {
            $error = 'Invalid hash ' . $ex->getMessage();
            Yii::error($error);
            return [null, $error];
        }

        return [$data, null];
    }

    /**
     * Checking pdf onlyoffice form
     *
     * @param File $file object
     * @return bool
     */
    public function isOnlyofficeForm($file)
    {
        if (!$file instanceof File || !$file->store->has()) {
            return false;
        }
        if ($this->getDocumentType($file) !== self::DOCUMENT_TYPE_PDF) {
            return false;
        }

        $limitDetect = 300;
        $onlyofficeFormMetaTag = 'ONLYOFFICEFORM';

        $content = file_get_contents($file->store->get(), false, null, 0, $limitDetect);

        $indexFirst = strpos($content, "%\xCD\xCA\xD2\xA9\x0D");
        if ($indexFirst === false) {
            return false;
        }

        $pFirst = substr($content, $indexFirst + 6);
        if (!str_starts_with($pFirst, "1 0 obj\n<<\n")) {
            return false;
        }

        $pFirst = substr($pFirst, 11);

        $indexStream = strpos($pFirst, "stream\x0D\x0A");
        $indexMeta = strpos($pFirst, $onlyofficeFormMetaTag);

        if ($indexStream === false || $indexMeta === false || $indexStream < $indexMeta) {
            return false;
        }

        $pMeta = substr($pFirst, $indexMeta);
        $pMeta = substr($pMeta, strlen($onlyofficeFormMetaTag) + 3);

        $indexMetaLast = strpos($pMeta, " ");
        if ($indexMetaLast === false) {
            return false;
        }

        $pMeta = substr($pMeta, $indexMetaLast + 1);

        $indexMetaLast = strpos($pMeta, " ");
        if ($indexMetaLast === false) {
            return false;
        }

        return true;
    }

    /**
     * Send request by URL with CURL method
     *
     * @param string $url
     * @param string $method
     * @param array $options
     * @return Response
     */
    public function request($url, $method = 'GET', $options = []): Response
    {
        $http = new Client(['transport' => 'yii\httpclient\CurlTransport']);
        $response = $http->createRequest()
            ->setUrl($url)
            ->setMethod($method)
            ->setOptions(CURLHelper::getOptions());

        if (substr($url, 0, strlen("https")) === "https" && $this->getVerifyPeerOff()) {
            $response->addOptions([
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,
            ]);
        }

        if (array_key_exists('headers', $options)) {
            foreach ($options['headers'] as $nameHeader => $header) {
                $response->addHeaders([$nameHeader => $header]);
            }
        }

        if (array_key_exists('body', $options)) {
            $response->setContent(Json::encode($options['body']));
        }

        return $response->send();
    }

    /**
     * Replace document server url to internal address
     *
     * @param string $url document server url
     * @return string
     */
    public function replaceDocumentServerUrlToInternal($url)
    {
        $documentServerUrl = $this->getInternalServerUrl();
        if (!empty($documentServerUrl)) {
            $from = $this->getServerUrl();

            if (!preg_match("/^https?:\/\//i", $from)) {
                $parsedUrl = parse_url($url);
                $from = $parsedUrl["scheme"] . "://" . $parsedUrl["host"] .
                    (array_key_exists("port", $parsedUrl) ? (":" . $parsedUrl["port"]) : "") . $from;
            }

            if ($from !== $documentServerUrl) {
                $url = str_replace($from, $documentServerUrl, $url);
            }
        }

        return $url;
    }

    public $languageCodes = [
        "default" => "default",
        "ar" => "ar-SA",
        "az" => "az-Latn-AZ",
        "bg" => "bg-BG",
        "ca" => "ca-ES",
        "cs" => "cs-CZ",
        "da" => "da-DK",
        "de" => "de-DE",
        "el" => "el-GR",
        "en_GB" => "en-GB",
        "en-US" => "en-US",
        "es" => "es-ES",
        "eu" => "eu-ES",
        "fi" => "fi-FI",
        "fr" => "fr-FR",
        "hu" => "hu-HU",
        "id" => "id-ID",
        "it" => "it-IT",
        "ja" => "ja-JP",
        "he" => "he-IL",
        "ko" => "ko-KR",
        "lv" => "lv-LV",
        "nb-NO" => "nb-NO",
        "nl" => "nl-NL",
        "pl" => "pl-PL",
        "pt_BR" => "pt-BR",
        "pt" => "pt-PT",
        "ro" => "ro-RO",
        "ru" => "ru-RU",
        "sq" => "sq-AL",
        "sk" => "sk-SK",
        "sl" => "sl-SI",
        "sr" => "sr-Cyrl-RS",
        "sv" => "sv-SE",
        "tr" => "tr-TR",
        "uk" => "uk-UA",
        "vi" => "vi-VN",
        "zh-CN" => "zh-CN",
        "zh-TW" => "zh-TW"
    ];

    private function convertResponceError($errorCode)
    {
        $errorMessage = "";

        switch ($errorCode) {
            case -20:
                $errorMessage = "Error encrypt signature";
                break;
            case -8:
                $errorMessage = "Invalid token";
                break;
            case -7:
                $errorMessage = "Error document request";
                break;
            case -6:
                $errorMessage = "Error while accessing the conversion result database";
                break;
            case -5:
                $errorMessage = "Incorrect password";
                break;
            case -4:
                $errorMessage = "Error while downloading the document file to be converted.";
                break;
            case -3:
                $errorMessage = "Conversion error";
                break;
            case -2:
                $errorMessage = "Timeout conversion error";
                break;
            case -1:
                $errorMessage = "Unknown error";
                break;
            case 0:
                break;
            default:
                $errorMessage = "ErrorCode = " . $errorCode;
                break;
        }

        throw new \Exception($errorMessage);
    }

    private function commandResponceError($errorCode)
    {
        $errorMessage = "";

        switch ($errorCode) {
            case 3:
                $errorMessage = "Internal server error";
                break;
            case 5:
                $errorMessage = "Command not correct";
                break;
            case 6:
                $errorMessage = "Invalid token";
                break;
            case 0:
                return;
            default:
                $errorMessage = "ErrorCode = " . $errorCode;
                break;
        }

        throw new \Exception($errorMessage);
    }
}
