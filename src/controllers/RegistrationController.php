<?php

namespace hiam\mrdp\controllers;

use hiam\actions\ConfirmEmail;
use yii\web\Controller;

class RegistrationController extends Controller
{
    public function actions()
    {
        return [
            'client-confirm-email' => [
                'class' => ConfirmEmail::class,
                'actionAttributeName' => 'what',
                'actionAttributeValue' => 'clientConfirmEmail',
                'usernameAttributeName' => 'client',
            ],
            'contact-confirm-email' => [
                'class' => ConfirmEmail::class,
                'actionAttributeName' => 'what',
                'actionAttributeValue' => 'contactConfirmEmail',
                'usernameAttributeName' => 'client',
            ],
        ];
    }
}
