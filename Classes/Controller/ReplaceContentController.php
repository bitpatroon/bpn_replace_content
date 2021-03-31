<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2017 Sjoerd Zonneveld <typo3@bitpatroon.nl>
 *                             <szonneveld@bitpatroon.nl>
 *  Date: 13-2-2017 14:19
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

namespace BPN\BpnReplaceContent\Controller;

use TYPO3\CMS\Core\Page\PageRenderer;

/**
 * Replaces ___tag___ with values from frontend user.
 */
class ReplaceContentController
{
    public const ALT_TAG_NAME = 'naam',
        ALT_TAG_FIRST_NAME = 'voornaam',
        ALT_TAG_MIDDLE_NAME = 'tussenvoegsel',
        ALT_TAG_LAST_NAME = 'achternaam',
        ALT_TAG_TELEPHONE = 'telefoonnummer';
    public const DEFAULT_VALUE_BEDRIJF = '[School]';
    public const DEFAULT_VALUE_TELEFOON = '[Telefoon]';
    public const DEFAULT_VALUE_USERNAAM = '[usernaam]';
    public const DEFAULT_VALUE_EMAIL = '[email]';
    public const DEFAULT_VALUE_ACHTERNAAM = '[achternaam]';
    public const DEFAULT_VALUE_TUSSENVOEGSEL = '[tussenvoegsel]';
    public const DEFAULT_VALUE_VOORNAAM = '[voornaam]';
    public const DEFAULT_VALUE_NAAM = '[Naam]';

    public const FIELD_ANY = 'any';
    public const FIELD_FIRSTNAME = 'first_name';
    public const FIELD_MIDDLENAME = 'middle_name';
    public const FIELD_LASTNAME = 'last_name';
    public const FIELD_TELEPHONE = 'telephone';
    public const FIELD_USERNAME = 'username';
    public const FIELD_COMPANY = 'company';
    public const FIELD_EMAIL = 'email';
    public const EMAIL_BLINDED = 'email_blinded';
    public const FIELD_HELPTAG = 'helptag';
    public const FIELD_NAME = 'name';
    public const FIELD_2SPACES = '2spaces';
    public const FIELD_ACTIONBUTTON = 'actionbutton';
    public const DATUM = 'datum';
    public const TIJD = 'tijd';
    public const DATUMTIJD = 'datumtijd';
    public const YEAR = 'year';
    public const MONTH = 'month';
    public const DAY = 'day';
    public const HOUR = 'hour';
    public const MINUTE = 'minute';
    public const SECOND = 'second';
    public const FIELD_HELP = 'help';

    /**
     * @var array
     */
    protected $additionalTagMapping = [
        self::FIELD_NAME       => self::ALT_TAG_NAME,
        self::FIELD_FIRSTNAME  => self::ALT_TAG_FIRST_NAME,
        self::FIELD_MIDDLENAME => self::ALT_TAG_MIDDLE_NAME,
        self::FIELD_LASTNAME   => self::ALT_TAG_LAST_NAME,
        self::FIELD_TELEPHONE  => self::ALT_TAG_TELEPHONE,
    ];

    /**
     * Hook for TSFE to perform content replace
     * @param array        $params
     * @param PageRenderer $pageRenderer
     */
    public function addProcessHooks(
        array $params,
        PageRenderer $pageRenderer
    ) {
        $tags = $this->getReplacementTags();
        $jsonData = json_encode($tags);
        $content = sprintf('const s1587413046568 = "%s";', base64_encode($jsonData));
        $pageRenderer->addJsInlineCode(1587413046568, $content);

        $pageRenderer->loadRequireJs();
        $pageRenderer->addJsFile(
            '/typo3conf/ext/bpn_replace_content/Resources/Public/JavaScript/BpnReplaceContent.js'
        );
    }


    /**
     * Method gets the replacement content
     *
     * @return array
     */
    public function getReplacementTags()
    {
        $result = $this->getPlaceholderTags();
        $result = $this->getFrontendUserFilledTags($result);
        // note: date time tags can NOT be cached
        $result = $this->fillAlternativeTagsWithValues($result);
        ksort($result);

        $help = [];
        foreach ($result as $key => $value) {
            $target = $result[$key];
            switch ($key) {
                case '2spaces':
                    $target .= sprintf('(%s)', htmlentities($result[$key]));
                    break;
            }
            $help[] = sprintf('<div><div class="label">___%s___</div><div class="value">%s</div></div>', $key, $target);
        }
        $help[] = sprintf(
            '<div><div class="label">Exclude</div><div class="value">%s</div></div>',
            htmlentities('{code}This text is not replaced{code}')
        );

        $result[self::FIELD_HELPTAG] = '___help___';
        $result[self::FIELD_HELP] = implode('', $help);

        return $result;
    }

    /**
     * @return array
     */
    protected function getPlaceholderTags()
    {
        return [
            self::FIELD_ANY         => self::DEFAULT_VALUE_NAAM,
            self::FIELD_NAME         => self::DEFAULT_VALUE_NAAM,
            self::FIELD_FIRSTNAME    => self::DEFAULT_VALUE_VOORNAAM,
            self::FIELD_MIDDLENAME   => self::DEFAULT_VALUE_TUSSENVOEGSEL,
            self::FIELD_LASTNAME     => self::DEFAULT_VALUE_ACHTERNAAM,
            self::FIELD_EMAIL        => self::DEFAULT_VALUE_EMAIL,
            self::EMAIL_BLINDED      => '',
            self::FIELD_USERNAME     => self::DEFAULT_VALUE_USERNAAM,
            self::FIELD_TELEPHONE    => self::DEFAULT_VALUE_TELEFOON,
            self::FIELD_COMPANY      => self::DEFAULT_VALUE_BEDRIJF,
            self::FIELD_2SPACES      => '&nbsp;&nbsp;',
            self::FIELD_ACTIONBUTTON => '<span class="button bpn-button___classes___">___content___</span>',
            self::DATUM              => date('d-m-Y'),
            self::TIJD               => date('H:i:s'),
            self::DATUMTIJD          => date('d-m-Y H:i:s'),
            self::YEAR               => date('Y'),
            self::MONTH              => date('m'),
            self::DAY                => date('d'),
            self::HOUR               => date('H'),
            self::MINUTE             => date('i'),
            self::SECOND             => date('s'),
        ];
    }

    /**
     * Adds additional tags to given result (eg naam => name, voornaam => first_name)
     *
     * @param array $result
     * @return array
     */
    protected function fillAlternativeTagsWithValues($result)
    {
        foreach ($this->additionalTagMapping as $original => $new) {
            if (isset($result[$new])) {
                continue;
            }
            $result[$new] = $result[$original];
        }
        return $result;
    }

    /**
     * Gets frontend user filled tags
     *
     * @param array $result
     * @return array|null
     */
    protected function getFrontendUserFilledTags($result)
    {
        if (empty($GLOBALS['TSFE']->fe_user->user['username'])) {
            return $result;
        }

        $name = $this->getFriendlyName(
            $GLOBALS['TSFE']->fe_user->user['first_name'],
            $GLOBALS['TSFE']->fe_user->user['middle_name'],
            $GLOBALS['TSFE']->fe_user->user['last_name']
        );
        $result = $this->replaceTag($result, 'name', $name, $GLOBALS['TSFE']->fe_user->user['name']);
        $result = $this->replaceTag($result, self::FIELD_FIRSTNAME, $GLOBALS['TSFE']->fe_user->user['first_name']);
        $result = $this->replaceTag(
            $result,
            self::FIELD_MIDDLENAME,
            $GLOBALS['TSFE']->fe_user->user['middle_name'],
            ''
        );
        $result = $this->replaceTag($result, self::FIELD_LASTNAME, $GLOBALS['TSFE']->fe_user->user['last_name']);
        $result = $this->replaceTag($result, self::FIELD_EMAIL, $GLOBALS['TSFE']->fe_user->user['email'], '');
        $result = $this->replaceTag(
            $result,
            'email_blinded',
            $this->makeEmailBlind($GLOBALS['TSFE']->fe_user->user['email'])
        );
        $result = $this->replaceTag($result, self::FIELD_TELEPHONE, $GLOBALS['TSFE']->fe_user->user['telephone'], '');
        $result = $this->replaceTag($result, self::FIELD_USERNAME, $GLOBALS['TSFE']->fe_user->user['username']);

        $email = $GLOBALS['TSFE']->fe_user->user['email'];
        $username = $GLOBALS['TSFE']->fe_user->user['username'];
        $anyName = $name ?: $email ?: $username ?: '-';
        $result = $this->replaceTag($result, self::FIELD_ANY, $anyName, $email);
        
        return $result;
    }

    /**
     * @param array  $result
     * @param string $key
     * @param string $value
     * @param string $defaultValue
     * @return array
     */
    private function replaceTag(array $result, string $key, $value, string $defaultValue = null): array
    {
        if (empty($value)) {
            $value = '';
            if (!empty($defaultValue) && is_string($defaultValue)) {
                $value = $defaultValue;
            }
        }
        $result[$key] = $value;
        return $result;
    }

    /**
     * Gets the friendly name
     * Format uses
     *      %[u]g          firstname (u for ucfirst)
     *      %[u]G          firstname with leading space (u for ucfirst)
     *      %[u]f          firstname (u for ucfirst)
     *      %[u]F          firstname with leading space (u for ucfirst)
     *      %[u]m          middlename (u for ucfirst)
     *      %[u]M          middlename with leading space (u for ucfirst)
     *      %[u]l          last name (u for ucfirst)
     *      %[u]L          lastname with leading space (u for ucfirst)
     *
     * Example:
     *      'some' 'random' 'name' -> format '%uf%M%uL' -> 'Some random Name'
     *
     * @param        $firstName
     * @param        $middleName
     * @param        $lastName
     * @param string $format the formatting to apply
     * @return string|null the name or null if not found.
     */
    public function getFriendlyName($firstName = '', $middleName = '', $lastName = '', $format = '%uf%M%uL')
    {
        // set default
        if (empty($format)) {
            $format = '%uf%M%uL';
        }
        // walk all tokens
        $name = '';
        $tokens = explode('%', $format);
        foreach ($tokens as $token) {
            if (empty($token)) {
                continue;
            }
            $transformToUpper = $token[0] === 'u';
            $letter = $token;
            if (strlen($token) == 2) {
                $letter = $token[1];
            }
            $originalLetter = $letter;
            $letter = strtolower($letter);
            // only add space if second word (and not first words were omitted)
            $addSpace = $letter != $originalLetter && !empty($name);
            switch ($letter) {
                case 'f':
                    $word = $firstName;
                    break;
                case 'm':
                    $word = $middleName;
                    break;
                case 'l':
                    $word = $lastName;
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Invalid token "%s" in formatting name "%s". ' .
                            'Allowed are %%f%%F%%m%%M%%l%%L%%uf%%uF%%um%%uM%%ul%%uL.',
                            $letter,
                            $format
                        ),
                        1486725192
                    );
            }
            if (!empty($word)) {
                $word = trim($word);
                if ($transformToUpper) {
                    $word = ucfirst($word);
                }
                if ($addSpace) {
                    $word = ' ' . $word;
                }
                $name .= $word;
            }
        }
        $name = $this->sanitize($name);
        return $name;
    }

    /**
     * Makes the email blind with blinkedToken as replacement
     * @param string $email
     * @param bool $prefix true to blind the part before the @. Default true
     * @param bool|false $dns true to blind the part after the @. Default false
     * @param string $blindingToken Default '******'
     * @return string
     */
    public function makeEmailBlind($email, $prefix = true, $dns = false, $blindingToken = '******')
    {
        if (!$prefix && !$dns) {
            return $email;
        }
        if (empty($email)) {
            return '';
        }

        $matches = [];

        if (!preg_match('/^([^@]{1,2})([^@]+)(@)(.{1,2})(.*)(\..+)$/i', $email, $matches)) {
            return $email;
        }

        unset($matches[0]);
        if ($prefix) {
            $matches[2] = $blindingToken;
        }
        if ($dns) {
            $matches[5] = $blindingToken;
        }

        return implode('', $matches);
    }

    /**
     * Sanitizes name
     * @param string $name
     * @return string
     */
    protected function sanitize($name)
    {
        return strip_tags($name);
    }

}
