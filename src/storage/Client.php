<?php
/**
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\mrdp\storage;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\Exception;
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
    public $send_me_news;

    public $email_confirmed;
    public $email_new;
    public $allowed_ips;
    public $totp_secret;

    public $password_hash;

    public static function tableName()
    {
        return '{{zclient}}';
    }

    public static function primaryKey()
    {
        return ['obj_id'];
    }

    public function rules()
    {
        return [
            [['username', 'email', 'password', 'first_name', 'last_name', 'email_new'], 'trim'],
            [['username', 'email'], 'filter', 'filter' => 'strtolower'],
            [['seller_id'], 'integer'],
            [['state'], 'trim'],
            [['email_confirmed', 'allowed_ips', 'totp_secret'], 'trim'],
            ['send_me_news', 'boolean'],
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
        if (empty($this->password)) {
            unset($this->password);
        }
        if (!empty($this->state)) {
            $this->state_id = new Expression("zref_id('state,client,{$this->state}')");
        }

        // If email or confirmed email got changed
        if (!empty($this->email_confirmed) && !empty($this->getDirtyAttributes(['email_confirmed', 'email']))) {
            $double = static::findOne(['email' => $this->email_confirmed]);
            if (empty($double) || $this->obj_id === $double->obj_id) {
                $this->email = $this->email_confirmed;
            }
            $this->saveValue('contact:email_new', '');
            $this->saveValue('contact:email_confirmed', $this->email_confirmed);
            $this->saveValue('contact:email_confirm_date', new Expression("date_trunc('second', now()::timestamp)::text"));
        }
        if (!empty($this->email_new)) {
            $this->saveValue('contact:email_new', $this->email_new);
        }
    }

    public function onAfterSave()
    {
        $this->id = $this->id ?: $this->getAgain()->id;
        $this->type = $this->type ?: $this->getAgain()->type;
        $send_news = $this->send_me_news === '0' ? '' : 1;

        $contact = Contact::findOne($this->id);
        $contact->setAttributes($this->getAttributes($contact->safeAttributes()));
        $contact->save();
        $this->saveValue('client,access:totp_secret', $this->totp_secret);
        $this->saveValue('client,access:allowed_ips', $this->allowed_ips);
        $this->saveValue('login_ips:panel', $this->allowed_ips);

        $this->saveValue('contact:policy_consent', 1);
        $this->saveValue('contact:gdpr_consent', 1);
        $this->saveValue('client,mailing:commercial', $send_news);
        $this->saveValue('client,mailing:newsletters', $send_news);

        $this->saveAnaliticsData();
    }

    private function saveAnaliticsData(): void
    {
        $utm = Yii::$app->session->get('utm_');
        if (empty($utm)) {
            return;
        }
        $this->saveValue('client,registration:referer', $utm);
    }

    protected $_again;

    public function getAgain()
    {
        /// XXX this crutch is needed bacause we use `zclient` view (not table)
        /// XXX and yii ActiveRecord doesn't populate model properly in this case
        if ($this->_again === null) {
            $this->_again = static::find()->whereUsername($this->username)->one();
        }

        return $this->_again;
    }

    public function saveValue($prop, $value)
    {
        $params = [
            'id' => $this->id,
            'prop' => $prop,
            'value' => $value,
        ];
        $sub = ':value';
        if ($value instanceof Expression) {
            $sub = (string)$value;
            unset($params['value']);
        }
        self::getDb()->createCommand("SELECT set_value(:id,:prop,$sub)", $params)->execute();
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

    /**
     * {@inheritdoc}
     */
    public function getPasswordHash()
    {
        return $this->password_hash;
    }

    public function getPassword_hash()
    {
        return $this->getAuthKey();
    }

    /**
     * @param string $email
     * @return bool
     */
    public function updateEmail(string $email): bool
    {
        if ($this->username) {
            try {
                if (Yii::$app->db->createCommand()
                    ->update('zclient', ['email' => $email], 'login = :login')
                    ->bindValue(':login', $this->username)
                    ->execute()) {
                    return true;
                }
            } catch (Exception $e) {
            }
        }

        return false;
    }

    protected static function filterCondition(array $condition, array $aliases = [])
    {
        /// XXX skip condition filtering
        return $condition;
    }
}
