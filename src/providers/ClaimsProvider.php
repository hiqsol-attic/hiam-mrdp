<?php

namespace hiam\mrdp\providers;

use hiam\mrdp\models\Identity;
use hiam\providers\ClaimsProviderInterface;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

class ClaimsProvider implements ClaimsProviderInterface
{
    public function getClaims(IdentityInterface $identity, string $claims): \stdClass
    {
        $claimed = explode(' ', trim($claims));

        $response = [];
        foreach ($this->claimBuilders() as $claim => $provider) {
            if (\in_array($claim, $claimed, true)) {
                $response = array_merge($response, $provider($identity));
            }
        }

        return (object)$response;
    }

    public function claimBuilders(): array
    {
        return [
            'profile' => function (Identity $identity) {
                return ArrayHelper::toArray($identity, [
                    Identity::class => [
                        'name' => function ($model) {
                            return trim("{$model->first_name} {$model->last_name}");
                        },
                        'username', 'email', 'email_confirmed',
                    ],
                ]);
            },
            'email' => function (Identity $identity) {
                return ArrayHelper::toArray($identity, [
                    Identity::class => ['email', 'email_confirmed'],
                ]);
            },
            'roles' => function (Identity $identity) {
                return ArrayHelper::toArray($identity, [
                    Identity::class => ['roles'],
                ]);
            },
        ];
    }
}
