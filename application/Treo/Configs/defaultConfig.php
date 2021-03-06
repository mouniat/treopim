<?php
/**
 * This file is part of EspoCRM and/or TreoPIM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2019 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: http://www.espocrm.com
 *
 * TreoPIM is EspoCRM-based Open Source Product Information Management application.
 * Copyright (C) 2017-2019 TreoLabs GmbH
 * Website: http://www.treopim.com
 *
 * TreoPIM as well as EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TreoPIM as well as EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word
 * and "TreoPIM" word.
 */

declare(strict_types=1);

namespace Treo\Configs;

return [
    'passwordSalt'           => 'some-salt',
    'database'               => [
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost',
        'port'     => '',
        'charset'  => 'utf8mb4',
        'dbname'   => '',
        'user'     => '',
        'password' => ''
    ],
    'version'                => '',
    'recordsPerPage'         => 200,
    'recordsPerPageSmall'    => 5,
    'lastViewedCount'        => 20,
    'decimalMark'            => '.',
    'thousandSeparator'      => ',',
    'useCache'               => false,
    'applicationName'        => 'TreoPim',
    'outboundEmailFromName'  => 'TreoPim',
    'languageList'           => [
        'en_US',
        'de_DE'
    ],
    'language'               => 'en_US',
    'currencyList'           => [
        0 => 'EUR'
    ],
    'defaultCurrency'        => 'EUR',
    'baseCurrency'           => 'EUR',
    'authenticationMethod'   => 'Espo',
    'globalSearchEntityList' =>
        [
            'Account',
            'Contact',
            'Lead',
            'Opportunity',
        ],
    'tabList'                => [
        0 => 'Association',
        1 => 'Attribute',
        2 => 'AttributeGroup',
        3 => 'Brand',
        4 => 'Category',
        5 => 'Product',
        6 => 'ProductFamily'
    ],
    'quickCreateList'        => [
        0 => 'Association',
        1 => 'Attribute',
        2 => 'AttributeGroup',
        3 => 'Brand',
        4 => 'Category',
        5 => 'Channel',
        6 => 'Product',
        7 => 'ProductFamily'
    ],
    'theme'                  => 'TreoDarkTheme',
    'dashboardLayout'        => [
        (object)[
            'name'   => 'My TreoPIM',
            'layout' => []
        ]
    ],
    'webMassUpdateMax'       => 200, // count of max massUpdate items for WEB
    'cronMassUpdateMax'      => 3000, // count of max massUpdate items for CRON
    'developMode'            => false,
    'exportDelimiter'        => ';',
    'timeZone'               => 'UTC'
];
