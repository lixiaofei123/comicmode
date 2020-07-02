<?php
namespace OCA\ComicMode\Db;

use JsonSerializable;

use OCP\AppFramework\Db\Entity;

class ComicReadRecord extends Entity implements JsonSerializable {

    protected $userId;
    protected $comicName;
    protected $chapterName;
    protected $lastReadTime;
    protected $comicDir;
    protected $fileId;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'comicName' => $this->comicName,
            'chapterName' => $this->chapterName,
            'lastReadTime' => $this->lastReadTime,
            'comicDir' => $this->comicDir,
            'fileId' => $this->fileId
        ];
    }
}