<?php
namespace OCA\ComicMode\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\ApiController;

use OCA\ComicMode\Db\ComicReadRecord;
use OCA\ComicMode\Service\ComicReadRecordService;

class RecordApiController extends ApiController{
    
    private $service;
    private $userId;

    use Errors;

    public function __construct(string $AppName, IRequest $request,
        ComicReadRecordService $service, $UserId){
        parent::__construct($AppName, $request);
        $this->service = $service;
        $this->userId = $UserId;
    }

    
    /**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
    public function create(int $fileId,string $comicDir,string $comicName, string $chapterName) {
        return $this->handleNotFound(function () use ($fileId,$comicDir, $comicName, $chapterName) {
            return $this->service->createOrUpdate($fileId,$comicDir, $comicName, $chapterName,$this->userId);
        });
    }

    /**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
    public function destroy(int $id) {
        return $this->handleNotFound(function () use ($id) {
            return $this->service->delete($this->userId,$id);
        });
    }

    /**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
    public function index() {
        return new DataResponse($this->service->findAll($this->userId));
    }

}
