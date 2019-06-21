<?php

namespace hiam\mrdp\validators;

use hiam\validators\PasswordValidatorInterface;
use Yii;
use yii\base\Model;
use yii\validators\Validator;
use hiam\mrdp\storage\Client;

class PasswordValidator extends Validator implements PasswordValidatorInterface
{
    /**
     * @var string login attribute name
     */
    public $loginAttribute = 'login';

    /**
     * @var string password attribute name
     */
    public $passwordAttribute = 'current_password';

    public function init()
    {
        parent::init();
        if ($this->message !== null) {
            return;
        }
        $this->message = Yii::t('hiam', 'The current password is incorrect');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if (!empty($model->{$this->loginAttribute}) && !empty($model->{$this->passwordAttribute})) {
            $found = Client::find()->andWhere([
                'username' => $model->{$this->loginAttribute},
                'password' => $model->{$this->passwordAttribute},
            ])->one();
            if (!$found) {
                $this->addError($model, $attribute, $this->message);
            }
        }
    }

    public function inlineFor(Model $model): \Closure
    {
        return function (string $attribute) use ($model) {
            $this->validateAttribute($model, $attribute);
        };
    }
}
