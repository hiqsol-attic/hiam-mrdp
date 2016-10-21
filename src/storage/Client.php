<?php

/*
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\mrdp\storage;

use Yii;

/**
 * Client model.
 *
 * @property integer $obj_id PK
 * @property integer $seller_id
 * @property string $password
 * @property string $email
 */
class Client extends \yii\db\ActiveRecord
{
    public $id;
    public $type;
    public $state;
    public $seller;
    public $username;
    public $last_name;
    public $first_name;

    public $allowed_ips;
    public $totp_secret;

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'first_name', 'last_name'], 'trim'],
            [['allowed_ips', 'totp_secret'], 'trim'],
        ];
    }

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'onBeforeInsert']);
        $this->on(static::EVENT_AFTER_INSERT,  [$this, 'onAfterSave']);
        $this->on(static::EVENT_AFTER_UPDATE,  [$this, 'onAfterSave']);
    }

    public function onBeforeInsert()
    {
        $seller = static::findOne(['username' => Yii::$app->params['user.seller']]);
        $this->login = $this->username ?: $this->email;
        $this->seller_id = $seller->id;
    }

    public function onAfterSave()
    {
        $this->id = $this->id ?: $this->obj_id;
        $this->type = $this->type ?: 'client';
        $contact = Contact::findOne($this->id);
        $contact->setAttributes($this->getAttributes());
        $contact->save();
        $this->saveValue('client,access:allowed_ips', $this->allowed_ips);
        $this->saveValue('client,access:totp_secret', $this->totp_secret);
    }

    public function saveValue($prop, $value)
    {
        self::getDb()->createCommand('SELECT set_value(:id,:prop,:value)', [
            'id' => $this->id,
            'prop' => $prop,
            'value' => $value,
        ])->execute();
    }

    public static function find()
    {
        return new ClientQuery(get_called_class());
    }
}
