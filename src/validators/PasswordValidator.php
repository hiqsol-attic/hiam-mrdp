<?php

namespace hiam\mrdp\validators;

use hiam\validators\PasswordValidatorInterface;
use Yii;
use yii\base\Model;
use yii\validators\Validator;

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
        if ($model->{$this->loginAttribute} && $model->{$this->passwordAttribute}) {
            $valid = Yii::$app->db->createCommand("
                    SELECT      zc.obj_id
                    FROM        zclient zc
                    WHERE       zc.state_id = ANY(state_ids('client', 'ok,active,new'))
                                AND NOT check_password('', zc.password)
                                AND login = :login AND check_password(:password, zc.password)
                ")->bindValues([':login' => $model->{$this->loginAttribute}, ':password' => $model->{$this->passwordAttribute}])->queryScalar();
            if (!$valid) {
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
