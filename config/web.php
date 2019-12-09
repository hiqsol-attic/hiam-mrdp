<?php
/**
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

return [
    'as saveReferralParams' => \hiam\mrdp\behaviors\SaveReferralParams::class,
    'aliases' => [
        '@runtime/var'      => $params['hiapi.var_dir'],
        '@runtime/tokens'   => '@runtime/var/tokens',
    ],
    'components' => [
        'user' => [
            'identityClass'   => \hiam\mrdp\models\Identity::class,
            'storageClasses'  => [
                'identity'   => \hiam\mrdp\storage\Client::class,
            ],
        ],
        'urlManager' => [
            'rules' => [
                'registration/<action>' => 'mrdp/registration/<action>',
            ],
        ],
        'log' => [
            'targets' => [
                'monitoring' => [
                    'except' => [
                        'yii\\web\\HttpException:403',
                    ],
                ],
            ],
        ],
    ],
    'modules' => [
        'mrdp' => [
            'class' => \hiam\mrdp\Module::class,
        ],
        'debug' => [
            'panels' => [
                'httpclient' => [
                    'class' => \yii\httpclient\debug\HttpClientPanel::class,
                ],
            ],
        ],
    ],
    'container' => [
        'singletons' => [
            \hiam\providers\ClaimsProviderInterface::class => \hiam\mrdp\providers\ClaimsProvider::class,
            \hiam\validators\PasswordValidatorInterface::class => \hiam\mrdp\validators\PasswordValidator::class,
            \hiam\validators\LoginValidatorInterface::class => \hiam\mrdp\validators\LoginValidator::class,
        ],
        'definitions' => [
            \hiam\mrdp\models\Identity::class => [
                'as saveReferralParams' => \hiam\mrdp\behaviors\SaveReferralParams::class,
            ],
        ],
    ],
];
