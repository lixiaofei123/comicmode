<?php
namespace OCA\ComicMode\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\ApiController;
use OCP\Files\Folder;
use OCP\Files\NotFoundException;


class ComicApiController extends ApiController{

    /** @var Folder */
	private $userFolder;

    public function __construct($appName,IRequest $request,Folder $userFolder) {
        parent::__construct($appName, $request);
        $this->userFolder = $userFolder;
    }

    /**
	 * @NoCSRFRequired
	 * @NoAdminRequired
	 */
    public function index($dir = '/'): JSONResponse {
        try{
            $file = $this->userFolder->get($dir);
            if($file instanceof Folder){
                $imgs = array();
                foreach($file -> getDirectoryListing() as $subfile){
                    $name = $subfile -> getName();
                    $path = $subfile -> getInternalPath();
                    $fid =  $subfile -> getId();
                    $lowname = strtolower($name);
                    if($this->endsWith($lowname,'jpg') ||$this->endsWith($lowname,'jpeg') ||$this->endsWith($lowname,'png')){
                        array_push($imgs,[
                            'name' => $name,
                            'id' => $fid,
                            'path' => $path,
                        ]);
                    }
                }
                $brotherDirs = array();
                if($dir != '/'){
                    $parent = $file -> getParent();
                    if($parent != null){
                        foreach($parent -> getDirectoryListing() as $subfile){
                            $name = $subfile -> getName();
                            if($subfile instanceof Folder){
                                array_push($brotherDirs,[
                                    'name' => $name,
                                ]);
                            }
                        }
                    }
                }
                return new JSONResponse([
                    'status' => 'OK',
                    'files' => $imgs,
                    'brothers' => $brotherDirs,
                ]);
            }else{
                return new JSONResponse([
                    'status' => 'error',
                    'reason' => 'This is not a directory'
                ]);
            }
        }catch(NotFoundException $e){
            return new JSONResponse([
                'status' => 'error',
                'reason' => 'Directory does not exist'
            ]);
        }
    }

    function endsWith($string, $endString) 
    { 
        $len = strlen($endString); 
        if ($len == 0) { 
            return true; 
        } 
        return (substr($string, -$len) === $endString); 
    }


}