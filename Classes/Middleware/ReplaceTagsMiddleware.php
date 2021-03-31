<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Sjoerd Zonneveld  <typo3@bitpatroon.nl>
 *  Date: 25-5-2020 14:12
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace BPN\BpnReplaceContent\Middleware;

use BPN\BpnReplaceContent\Controller\ReplaceContentController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ReplaceTagsMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);

        /** @var TypoScriptFrontendController $controller */
        $controller = $GLOBALS['TSFE'];

        $headerComment = trim($controller->config['config']['headerComment2'] ?? '');

        if ($headerComment) {

            /** @var ReplaceContentController $replaceContentController */
            $replaceContentController = GeneralUtility::makeInstance(ReplaceContentController::class);
            $tags = $replaceContentController->getReplacementTags();
            foreach ($tags as $tagName => $tagValue) {
                if (stripos($headerComment, '___' . $tagName . '___') !== false) {
                    $headerComment = preg_replace('/___' . $tagName . '___/is', $tagValue, $headerComment);
                }
            }

            $pageRenderer->addInlineComment(
                "\t" . str_replace(
                    LF,
                    LF .
                    "\t",
                    $headerComment
                ) . LF
            );
        }
        return $handler->handle($request);
    }
}
