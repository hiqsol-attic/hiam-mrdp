<?php

namespace hiam\mrdp\validators;

use hiam\validators\LoginValidatorInterface;
use Yii;
use yii\base\Model;
use yii\validators\Validator;

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
        $this->message = Yii::t('hiam', 'Incorrect username');
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if ($model->{$this->loginAttribute}) {
            $valid = Yii::$app->db->createCommand("
                    SELECT      zc.obj_id
                    FROM        zclient zc
                    WHERE       zc.state_id = ANY(state_ids('client', 'ok,active,new'))
                                AND NOT check_password('', zc.password)
                                AND login = :login
                ")->bindValues([':login' => $model->{$this->loginAttribute}])->queryScalar();
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
