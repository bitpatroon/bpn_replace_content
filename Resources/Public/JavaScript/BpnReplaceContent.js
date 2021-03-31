/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Sjoerd Zonneveld  <typo3@bitpatroon.nl>
 *  Date: 22-3-2020 23:01
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
(function ($) {

    var self = this;

    /** array */
    self.replacementDefinitions = null;

    /**
     * @type {bool}
     */
    self.debug = false;

    /**
     * @param {jQuery} $element
     */
    self.replaceContent = function ($element) {
        let html = $element.html();
        var regExp = new RegExp(/___(\w+)___/);
        let match = regExp.exec(html);
        if (match === null) {
            console.debug('BpnReplaceContent.js:1589408061233:', 'not found');
            return;
        }
        let key = match[1];
        let value = self.replacementDefinitions[key];
        $element.html(value);
        $element.addClass('tx-replace-processed');
    };

    /**
     *
     */
    self.processTags = function () {
        $('.tx-replace-target').each(function () {
            var $element = $(this);
            if ($element.parent().hasClass('tx-replace-ignore')) {
                return;
            }
            self.replaceContent($element);
        });
    };

    /**
     * @param {jQuery} $element
     * @param {string} key
     * @param {string} value
     */
    self.protectCode = function ($element, key, value) {
        const html = $element.html();
        let newHtml = $element.html();
        var regExp = new RegExp(/\{code\}([^\{]+)\{code\}/, 'gi');
        while ((match = regExp.exec(html)) !== null) {
            let replacement = '<span class="tx-replace-ignore">' + match[1] + '</span>';
            newHtml = newHtml.replace(match[0], replacement);
        }
        $element.html(newHtml);
    };

    self.protectCodeTags = function () {
        $('footer,header,section').find(
            'p:contains("{code}"),' +
            'li:contains("{code}")'
        ).each(function () {
            var $element = $(this);
            self.protectCode($element);
        });
    };

    self.prepareTag = function ($element) {
        let html = $element.html();
        let newHtml = $element.html();
        let regExp = new RegExp(/___\w+?___/, 'gi');
        while ((match = regExp.exec(html)) !== null) {
            let replacement = '<span class="tx-replace-target">' + match[0] + '</span>';
            newHtml = newHtml.replace(match[0], replacement);
        }
        $element.html(newHtml);
    };

    self.prepare = function () {
        $('footer,header,section,main').find(
            'p:contains("___"),' +
            'li:contains("___")'
        ).each(function () {
            let $element = $(this);
            self.prepareTag($element);
        });
    };

    /**
     * load
     */
    self.execute = function () {
        self.replacementDefinitions = self.deflate() || null;
        if (self.replacementDefinitions === null) {
            return;
        }
        self.prepare();
        self.protectCodeTags();
        self.processTags();
    };

    /**
     * @returns {null|array}
     */
    self.deflate = function () {
        let base64Code = s1587413046568 || null;
        if (base64Code === null) {
            return null;
        }
        try {
            let code = atob(base64Code);
            return JSON.parse(code);
        } catch {
            // do noting
            return null;
        }
    };

    $(document).ready(function () {
        self.execute();
    });
})(jQuery);
