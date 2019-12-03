<?php
/**
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\mrdp\models;

use hiam\behaviors\SaveReferralParams;

/**
 * Identity model for MRDP database.
 *
 * @property string $roles
 * @property string $seller
 * @property integer $seller_id
 */
class Identity extends \hiam\models\Identity
{
    public $roles;
    public $seller;
    public $seller_id;
    public $email_confirmed;
    public $email_new;
    public $verified;
    public $send_me_news;
    public $referralParams;

    protected $activeStates = ['ok', 'active'];

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            ['seller_id',       'integer'],

            ['seller',          'trim'],
            ['seller',          'string', 'min' => 2, 'max' => 64],

            ['roles',           'trim'],
            ['email_confirmed', 'email'],
            ['verified',        'boolean'],
            ['send_me_news',    'boolean'],
        ]);
    }

    public function isActive()
    {
        return \in_array($this->state, $this->activeStates, true);
    }

    public function isEmailConfirmed()
    {
        return !empty($this->email_confirmed);
    }

    public function setNewUnconfirmedEmail(string $newEmail): bool
    {
        $this->email_new = $newEmail;

        return true;
    }

    public function setConfirmedEmail(string $email)
    {
        $this->state = 'ok';
        $this->email = $email;
        $this->email_confirmed = $email;
        $this->save();
    }
}
