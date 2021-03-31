<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE !== 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-postTransform'][] =
        \BPN\BpnReplaceContent\Controller\ReplaceContentController::class . '->addProcessHooks';
}
