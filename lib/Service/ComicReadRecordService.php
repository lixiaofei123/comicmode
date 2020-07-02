<?php
namespace OCA\ComicMode\Service;

use Exception;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

use OCA\ComicMode\Db\ComicReadRecord;
use OCA\ComicMode\Db\ComicReadRecordMapper;

class ComicReadRecordService{

    private $mapper;

    public function __construct(ComicReadRecordMapper $mapper){
        $this->mapper = $mapper;
    }

    private function handleException ($e) {
        if ($e instanceof DoesNotExistException ||
            $e instanceof MultipleObjectsReturnedException) {
            throw new NotFoundException($e->getMessage());
        } else {
            throw $e;
        }
    }

    public function findByFileId($userId, int $fileId) {
        try {
            return $this->mapper->findByFileId($userId, $fileId);
        } catch(Exception $e) {
            if($e instanceof DoesNotExistException){
                return null;
            }else{
                $this->handleException($e);
            }
        }
    }

    public function findAll($userId) {
        try {
            return $this->mapper->findAll($userId);
        } catch(Exception $e) {
            if($e instanceof DoesNotExistException){
                return null;
            }else{
                $this->handleException($e);
            }
        }
    }

    public function create(int $fileId,string $comicDir,string $comicName, string $chapterName, string $userId){
        $record = new ComicReadRecord();
        $record->setFileId($fileId);
        $record->setComicDir($comicDir);
        $record->setComicName($comicName);
        $record->setChapterName($chapterName);
        $record->setLastReadTime(time());
        $record->setUserId($userId);
        return $this->mapper->insert($record);
    }

    public function update(ComicReadRecord $record, string $comicDir,string $comicName, string $chapterName) {
        try {
            $record->setComicDir($comicDir);
            $record->setComicName($comicName);
            $record->setChapterName($chapterName);
            $record->setLastReadTime(time());
            return $this->mapper->update($record);
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }

    public function createOrUpdate(int $fileId,string $comicDir,string $comicName, string $chapterName, string $userId) {
        try{
            $record = $this->findByFileId($userId, $fileId);
            if($record == null){
                return $this->create($fileId,$comicDir,$comicName,$chapterName,$userId);
            }else{
                return $this->update($record,$comicDir,$comicName,$chapterName);
            }
        }catch(Exception $e){
            $this->handleException($e);
        }
    }

    public function delete($userId, $fileId) {
        try {
            $record = $this->findByFileId($userId, $fileId);
            $this->mapper->delete($record);
            return $record;
        } catch(Exception $e) {
            $this->handleException($e);
        }
    }


}