<?php

/*
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

return [
    'components' => [
        'user' => [
            'identityClass'   => \hiam\mrdp\models\Identity::class,
            'storageClasses'  => [
                'identity'   => \hiam\mrdp\storage\Client::class,
            ],
        ],
    ],
];
