<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\onlyoffice\services;

use humhub\modules\file\libs\FileHelper;
use humhub\modules\file\models\File;
use humhub\modules\onlyoffice\Module;
use Yii;
use yii\helpers\Url;

/**
 * Service to work with a file in the ONLYOFFICE editors
 *
 * @since 4.0.1
 */
class FileService
{
    public function __construct(private readonly File $file)
    {
    }

    public static function instance(File $file): self
    {
        return new self($file);
    }

    /**
     * Returns the document type (word, cell, slide, pdf, diagram) or null if the file type is not supported
     */
    public function getDocumentType(): ?string
    {
        $extension = $this->getExtension();
        $formats = $this->getModule()->formats();

        if (in_array($extension, $formats->spreadsheetExtensions)) {
            return Module::DOCUMENT_TYPE_SPREADSHEET;
        } elseif (in_array($extension, $formats->presentationExtensions)) {
            return Module::DOCUMENT_TYPE_PRESENTATION;
        } elseif (in_array($extension, $formats->textExtensions)) {
            return Module::DOCUMENT_TYPE_TEXT;
        } elseif (in_array($extension, $formats->pdfExtensions)) {
            return Module::DOCUMENT_TYPE_PDF;
        } elseif (in_array($extension, $formats->diagramExtensions)) {
            return Module::DOCUMENT_TYPE_DIAGRAM;
        }

        return null;
    }

    public function canEdit(): bool
    {
        $module = $this->getModule();

        return in_array(
            $this->getExtension(),
            array_merge($module->getforceEditTypes(), $module->formats()->editableExtensions),
        );
    }

    public function canConvert(): bool
    {
        return in_array($this->getExtension(), $this->getModule()->formats()->convertableExtensions);
    }

    /**
     * Whether the file type can be opened in the editors (this is not the file access permission)
     */
    public function canView(): bool
    {
        return !empty($this->getDocumentType());
    }

    /**
     * Returns the unique document key, the key is generated and stored if the file has no one yet
     */
    public function generateDocumentKey(): string
    {
        if (!empty($this->file->onlyoffice_key)) {
            return $this->file->onlyoffice_key;
        }

        $key = substr(strtolower(md5(Yii::$app->security->generateRandomString(20))), 0, 20);
        $this->file->updateAttributes(['onlyoffice_key' => $key]);

        return $key;
    }

    /**
     * Sends the file to the conversion service
     */
    public function convert($ts, $toExt = null, $async = true): array
    {
        $module = $this->getModule();
        $key = $this->generateDocumentKey();

        $user = Yii::$app->user->getIdentity();
        $userGuid = null;
        if (isset($user->guid)) {
            $userGuid = $user->guid;
        }

        $docHash = $module->generateHash($key, $userGuid);

        $fromExt = $this->getExtension();

        $downloadUrl = Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], true);
        if (!empty($module->getStorageUrl())) {
            $downloadUrl = $module->getStorageUrl() . Url::to(['/onlyoffice/backend/download', 'doc' => $docHash], false);
        }

        if (is_null($toExt)) {
            $toExt = $module->formats()->convertsTo[$fromExt];
        }

        return $module->convertService($downloadUrl, $fromExt, $toExt, $key . $ts, $async);
    }

    /**
     * Checking pdf onlyoffice form
     */
    public function isOnlyofficeForm(): bool
    {
        if (!$this->file->store->has()) {
            return false;
        }
        if ($this->getDocumentType() !== Module::DOCUMENT_TYPE_PDF) {
            return false;
        }

        $limitDetect = 300;
        $onlyofficeFormMetaTag = 'ONLYOFFICEFORM';

        $stream = $this->getStream();
        $content = stream_get_contents($stream, $limitDetect, 0);
        fclose($stream);

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
     * Returns the content stream of the file
     *
     * Since HumHub 1.19 the storage path is relative to the file system and can not be opened directly.
     *
     * @return resource
     */
    public function getStream()
    {
        $store = $this->file->store;

        return method_exists($store, 'getContentStream') ? $store->getContentStream() : fopen($store->get(), 'rb');
    }

    /**
     * Returns the file name after the conversion (the name without the last extension + the new extension)
     */
    public function getConvertedName(): string
    {
        return pathinfo($this->file->file_name, PATHINFO_FILENAME) . '.'
            . $this->getModule()->formats()->convertsTo[$this->getExtension()];
    }

    private function getExtension(): string
    {
        return strtolower(FileHelper::getExtension($this->file));
    }

    private function getModule(): Module
    {
        return Yii::$app->getModule('onlyoffice');
    }
}
