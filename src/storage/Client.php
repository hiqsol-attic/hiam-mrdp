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
use yii\db\Expression;

/**
 * Client model.
 *
 * @property integer $obj_id PK
 * @property integer $id synced with obj_id
 * @property integer $seller_id
 * @property string $password
 * @property string $email
 */
class Client extends \yii\db\ActiveRecord
{
    public $type;
    public $state;
    public $roles;
    public $seller;
    public $username;
    public $last_name;
    public $first_name;

    public $email_confirmed;
    public $allowed_ips;
    public $totp_secret;

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'first_name', 'last_name'], 'trim'],
            [['state'], 'trim'],
            [['email_confirmed', 'allowed_ips', 'totp_secret'], 'trim'],
        ];
    }

    public function init()
    {
        parent::init();
        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'onBeforeInsert']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'onBeforeSave']);
        $this->on(static::EVENT_AFTER_INSERT,  [$this, 'onAfterSave']);
        $this->on(static::EVENT_AFTER_UPDATE,  [$this, 'onAfterSave']);
    }

    public function onBeforeInsert()
    {
        $seller = static::findOne(['username' => Yii::$app->params['user.seller']]);
        $this->login = $this->username ?: $this->email;
        $this->seller_id = $seller->id;
        $this->onBeforeSave();
    }

    public function onBeforeSave()
    {
        if (!empty($this->state)) {
            $this->state_id = new Expression($this->state==='ok'
                ? "coalesce(state_id('client,ok'),state_id('client,active'))"
                : "state_id('client,{$this->state}')"
            );
        }
        if ($this->email_confirmed) {
            $this->email = $this->email_confirmed;
            $this->saveValue('contact:email_new', '');
            $this->saveValue('contact:email_confirmed', $this->email_confirmed);
            $this->saveValue('contact:email_confirm_date', new Expression('now()::text'));
        }
    }

    public function onAfterSave()
    {
        $this->id = $this->id ?: $this->obj_id;
        $this->type = $this->type ?: 'client';
        $contact = Contact::findOne($this->id);
        $contact->setAttributes($this->getAttributes($contact->safeAttributes()));
        $contact->save();
        $this->saveValue('client,access:totp_secret', $this->totp_secret);
        $this->saveValue('client,access:allowed_ips', $this->allowed_ips);
        $this->saveValue('login_ips:panel', $this->allowed_ips);
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

    public function setId($value)
    {
        $this->obj_id = $value;
    }

    public function getId()
    {
        return $this->obj_id;
    }

    public function getSeller_id()
    {
        return $this->reseller_id;
    }
}
