<?php

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
