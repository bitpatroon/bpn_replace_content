<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2018 Sjoerd Zonneveld <typo3@bitpatroon.nl>
 *  Date: 8-2-2018 14:39
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

namespace SPL\SplReplaceContent\Tests\Functional\Controller;

use SPL\SplDomain\Domain\Model\FrontendUser;
use SPL\SplLibrary\AccessControl\AuthorizationService;
use SPL\SplLibrary\Utility\ObjectHelper;

use SPL\SplLibrary\Utility\Test\Helper;
use SPL\SplReplaceContent\Controller\ReplaceContentController;
use TYPO3\CMS\Core\Tests\FunctionalTestCase;

class ReplaceContentControllerTest extends FunctionalTestCase
{

    protected $testExtensionsToLoad = [
        'typo3conf/ext/cache_all',
    ];

    /**
     * @test
     * @dataProvider dataProvider_tagsAreTranslatedCorrectly
     * @param array $userData
     * @param bool $isDump true if the data is a dump an is restoreable (see typo3log/errors/XXXXXX_ReplaceContentController.log)
     * @param bool $expectedResult
     * @throws \ReflectionException
     */
    public function tagsAreTranslatedCorrectly($userData, $isDump, $expectedResult)
    {
        $this->markTestSkipped('Must be moved to acceptance tests because TSFE is required.');

        /** @var FrontendUser $frontendUserModel */
        $frontendUserModel = null;

        if ($userData instanceof \TYPO3\CMS\Extbase\Domain\Model\FrontendUser && !$isDump) {
            $frontendUserModel = $userData;
        } elseif ($isDump && is_array($userData)) {
            $frontendUserModel = ObjectManagerHelper::get(FrontendUser::class);
            $frontendUserModel = ObjectHelper::populateObject($frontendUserModel, $userData, true);
        } else {
            $this->fail('Cannot process userdata! [1518100410730]');
        }

        /** @var AuthorizationService|\PHPUnit_Framework_MockObject_MockObject $authorizationService */
        $authorizationService = $this->getMockObjectGenerator()->getMock(AuthorizationService::class, ['getCurrentlyLoggedInFrontendUser']);
        $authorizationService->expects($this->once())
            ->method('getCurrentlyLoggedInFrontendUser')
            ->willReturn($frontendUserModel);

        /** @var ReplaceContentController $replaceContentController */
        $replaceContentController = ObjectManagerHelper::get(ReplaceContentController::class);

        $result = [];
        $result = Helper::invokeProtected($replaceContentController, 'getFrontendUserFilledTags', [$result]);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
        foreach ($result as $fieldId => $value) {
            $this->assertEquals($expectedResult[$fieldId], $result[$fieldId]);
        }
    }

    /**
     * array
     */
    public function dataProvider_tagsAreTranslatedCorrectly()
    {
        $userWithInvalidSettings1 = [
            'message'      => 'Some(tags) were not translated. [1516193662778]',
            'class'        => FrontendUser::class,
            'frontEndUser' => ['account_type'                 => null,
                               'active_expiring_groups_uids'  => [],
                               'address'                      => '',
                               'all_expiring_groups'          => [],
                               'city'                         => '',
                               'classes'                      => null,
                               'comments'                     => null,
                               'company'                      => '',
                               'composed_full_name'           => '',
                               'country'                      => '',
                               'disable'                      => null,
                               'email'                        => '',
                               'email_verified'               => null,
                               'end_time'                     => null,
                               'expiring_groups'              => null,
                               'fax'                          => '',
                               'first_name'                   => '',
                               'gender'                       => null,
                               'hidden'                       => null,
                               'image'                        => '',
                               'ims_lis_source_id'            => null,
                               'initials'                     => null,
                               'is_account_dirty'             => null,
                               'is_expired_or_hidden'         => false,
                               'is_online'                    => null,
                               'last_name'                    => '',
                               'lastlogin'                    => null,
                               'license_change_count'         => null,
                               'license_switch_date'          => null,
                               'lock_to_domain'               => '',
                               'middle_name'                  => '',
                               'mobile_phone_number'          => null,
                               'name'                         => '',
                               'password'                     => '',
                               'pid'                          => 10652,
                               'preferred_groups'             => null,
                               'primaire_opleidingsgebied'    => null,
                               'primary_qualification'        => null,
                               'redirect_pid'                 => null,
                               'send_news_letter'             => null,
                               'send_news_letter_examination' => null,
                               'send_news_letter_training'    => null,
                               'spleck_unactivated_licenses'  => null,
                               'start_time'                   => null,
                               'student_number'               => null,
                               'switch_credit_date'           => null,
                               'telephone'                    => '',
                               'title'                        => '',
                               'uid'                          => 133471,
                               'user_group_uids'              => [],
                               'usergroup'                    => null,
                               'username'                     => '',
                               'verification_code'            => null,
                               'www'                          => '',
                               'zip'                          => '',],
            'userId'       => '133471',
        ];
        $userWithInvalidSettings2 = [
            'account_type'                 => null,
            'active_expiring_groups_uids'  => [],
            'address'                      => '',
            'all_expiring_groups'          => [],
            'city'                         => '',
            'classes'                      => null,
            'comments'                     => null,
            'company'                      => 'Stichting Praktijkleren',
            'composed_full_name'           => '',
            'country'                      => '',
            'disable'                      => null,
            'email'                        => 'docent@bitpatroon.nl',
            'email_verified'               => null,
            'end_time'                     => null,
            'expiring_groups'              => null,
            'fax'                          => '',
            'first_name'                   => 'Sjoerd',
            'gender'                       => null,
            'hidden'                       => 0,
            'image'                        => '',
            'ims_lis_source_id'            => null,
            'initials'                     => null,
            'is_account_dirty'             => 0,
            'is_expired_or_hidden'         => false,
            'is_online'                    => null,
            'last_name'                    => 'Zonneveld',
            'lastlogin'                    => null,
            'license_change_count'         => null,
            'license_switch_date'          => null,
            'lock_to_domain'               => '',
            'middle_name'                  => '',
            'mobile_phone_number'          => null,
            'name'                         => '',
            'password'                     => '',
            'pid'                          => 10652,
            'preferred_groups'             => null,
            'primaire_opleidingsgebied'    => null,
            'primary_qualification'        => null,
            'redirect_pid'                 => null,
            'send_news_letter'             => null,
            'send_news_letter_examination' => null,
            'send_news_letter_training'    => null,
            'spleck_unactivated_licenses'  => null,
            'start_time'                   => null,
            'student_number'               => null,
            'switch_credit_date'           => null,
            'telephone'                    => '',
            'title'                        => '',
            'uid'                          => 133471,
            'user_group_uids'              => [],
            'usergroup'                    => null,
            'username'                     => 'docent@bitpatroon.nl',
            'verification_code'            => null,
            'www'                          => '',
            'zip'                          => ''
        ];

        return [
            [
                $userWithInvalidSettings1['frontEndUser'],
                true,
                [
                    'name'          => '[name niet ingevuld]',
                    'first_name'    => '[first_name niet ingevuld]',
                    'middle_name'   => '',
                    'last_name'     => '[last_name niet ingevuld]',
                    'email'         => '',
                    'email_blinded' => '[email_blinded niet ingevuld]',
                    'telephone'     => '',
                    'username'      => '[username niet ingevuld]',
                ]
            ],
            [
                $userWithInvalidSettings2,
                true,
                [
                    'name'          => 'Sjoerd Zonneveld',
                    'first_name'    => 'Sjoerd',
                    'middle_name'   => '',
                    'last_name'     => 'Zonneveld',
                    'email'         => 'docent@bitpatroon.nl',
                    'email_blinded' => 'do******@bitpatroon.nl',
                    'telephone'     => '',
                    'username'      => 'docent@bitpatroon.nl',
                ]
            ]
        ];
    }

    /**
     * @test
     * @dataProvider dataProvider_replacementTagsArFalselyRetrievedFromCache
     * @param array $userData
     * @param       $expectedResult
     * @throws \ReflectionException
     */
    public function replacementTagsArFalselyRetrievedFromCache($userData, $expectedResult)
    {
        $this->markTestSkipped('Must be moved to acceptance tests because TSFE is required.');
        /** @var FrontendUser $frontendUserModel */
        $frontendUserModel = null;

        if (is_array($userData)) {
            $frontendUserModel = ObjectManagerHelper::get(FrontendUser::class);
            $frontendUserModel = ObjectHelper::populateObject($frontendUserModel, $userData, true);
        } else {
            $this->fail('Cannot process userdata! [1518104357849]');
        }

        /** @var AuthorizationService|\PHPUnit_Framework_MockObject_MockObject $authorizationService */
        $authorizationService = $this->getMockObjectGenerator()->getMock(AuthorizationService::class, ['getCurrentlyLoggedInFrontendUser']);
        $authorizationService->expects($this->once())
            ->method('getCurrentlyLoggedInFrontendUser')
            ->willReturn($frontendUserModel);

        /** @var ReplaceContentController $replaceContentController */
        $replaceContentController = ObjectManagerHelper::get(ReplaceContentController::class);

        $userId = (int)$userData['uid'];
        $cacheKey = \SPL\CacheAll\Utility\Cache::getCacheKey(
            ReplaceContentController::class,
            ReplaceContentController::class . '::getFrontendUserFilledTags',
            [$userId]
        );
        \SPL\CacheAll\Utility\Cache::set($cacheKey, $expectedResult, null, 180);

        $result = Helper::invokeProtected($replaceContentController, 'getFrontendUserFilledTags', [$expectedResult]);

        $this->assertNotEmpty($result);
        $this->assertInternalType('array', $result);
    }

    /**
     * array
     */
    public function dataProvider_replacementTagsArFalselyRetrievedFromCache()
    {
        $userWithInvalidSettings2 = [
            'account_type'                 => null,
            'active_expiring_groups_uids'  => [],
            'address'                      => '',
            'all_expiring_groups'          => [],
            'city'                         => '',
            'classes'                      => null,
            'comments'                     => null,
            'company'                      => 'Stichting Praktijkleren',
            'composed_full_name'           => '',
            'country'                      => '',
            'disable'                      => null,
            'email'                        => 'docent@bitpatroon.nl',
            'email_verified'               => null,
            'end_time'                     => null,
            'expiring_groups'              => null,
            'fax'                          => '',
            'first_name'                   => 'Sjoerd',
            'gender'                       => null,
            'hidden'                       => 0,
            'image'                        => '',
            'ims_lis_source_id'            => null,
            'initials'                     => null,
            'is_account_dirty'             => 0,
            'is_expired_or_hidden'         => false,
            'is_online'                    => null,
            'last_name'                    => 'Zonneveld',
            'lastlogin'                    => null,
            'license_change_count'         => null,
            'license_switch_date'          => null,
            'lock_to_domain'               => '',
            'middle_name'                  => '',
            'mobile_phone_number'          => null,
            'name'                         => '',
            'password'                     => '',
            'pid'                          => 10652,
            'preferred_groups'             => null,
            'primaire_opleidingsgebied'    => null,
            'primary_qualification'        => null,
            'redirect_pid'                 => null,
            'send_news_letter'             => null,
            'send_news_letter_examination' => null,
            'send_news_letter_training'    => null,
            'spleck_unactivated_licenses'  => null,
            'start_time'                   => null,
            'student_number'               => null,
            'switch_credit_date'           => null,
            'telephone'                    => '',
            'title'                        => '',
            'uid'                          => 123123123,
            'user_group_uids'              => [],
            'usergroup'                    => null,
            'username'                     => 'docent@bitpatroon.nl',
            'verification_code'            => null,
            'www'                          => '',
            'zip'                          => ''
        ];

        return [
            [
                $userWithInvalidSettings2,
                [
                    'name'          => 'Sjoerd Zonneveld',
                    'first_name'    => 'Sjoerd',
                    'middle_name'   => '',
                    'last_name'     => 'Zonneveld',
                    'email'         => 'docent@bitpatroon.nl',
                    'email_blinded' => 'do******@bitpatroon.nl',
                    'telephone'     => '',
                    'username'      => ReplaceContentController::DEFAULT_VALUE_USERNAAM,
                ]
            ],
            [
                $userWithInvalidSettings2,
                [
                    'name'          => 'Sjoerd Zonneveld',
                    'first_name'    => 'Sjoerd',
                    'middle_name'   => '',
                    'last_name'     => 'Zonneveld',
                    'email'         => 'docent@bitpatroon.nl',
                    'email_blinded' => 'do******@bitpatroon.nl',
                    'telephone'     => '',
                    'username'      => 'docent@bitpatroon.nl',
                    'company'       => ReplaceContentController::DEFAULT_VALUE_BEDRIJF,
                ]
            ]
        ];
    }
}
