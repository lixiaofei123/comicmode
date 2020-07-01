<?php

namespace OCA\ComicMode\AppInfo;

use OCP\Util;

\OC::$server->getEventDispatcher()->addListener(
    'OCA\Files::loadAdditionalScripts',
    function () {
        Util::addScript('comicmode', 'main');
    }
);

\OC::$server->getEventDispatcher()->addListener(
    'OCA\Files_Sharing::loadAdditionalScripts',
    function () {
        Util::addScript('comicmode', 'main');
    }
);



