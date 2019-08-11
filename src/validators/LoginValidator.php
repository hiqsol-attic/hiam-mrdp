<?php

namespace hiam\mrdp\validators;

use hiam\validators\LoginValidatorInterface;
use Yii;
use yii\base\Model;
use yii\validators\Validator;
use hiam\mrdp\storage\Client;

class LoginValidator extends Validator implements LoginValidatorInterface
{
    /**
     * @var string login attribute name
     */
    public $loginAttribute = 'username';

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
        $this->message = Yii::t('hiam', 'Incorrect login');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if (!empty($model->{$this->loginAttribute})) {
            $found = Client::find()->andWhere([
                'username' => $model->{$this->loginAttribute},
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
