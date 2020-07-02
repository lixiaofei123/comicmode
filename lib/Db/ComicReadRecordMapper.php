<?php
namespace OCA\ComicMode\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;


class ComicReadRecordMapper extends QBMapper {
    
    public function __construct(IDbConnection $db) {
        parent::__construct($db, 'comicreadrecord', ComicReadRecord::class);
    }

    public function findByFileId(string $userId,int $fileId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
           ) -> andWhere(
            $qb->expr()->eq('file_id', $qb->createNamedParameter($fileId))
           );

        return $this->findEntity($qb);
    }

    public function findAll(string $userId) {
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
           );

        return $this->findEntities($qb);
    }

}