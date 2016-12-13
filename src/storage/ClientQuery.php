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

class ClientQuery extends \yii\db\ActiveQuery
{
    public function init()
    {
        parent::init();
        $this
            ->select([
                'c.obj_id       AS id',
                'c.login        AS username',
                'r.login        AS seller',
                'c.seller_id    AS seller_id',
                'y.name         AS type',
                'z.name         AS state',
                'e.roles        AS roles',
                'k.first_name   AS first_name',
                'k.last_name    AS last_name',
                't.value        AS totp_secret',
                'coalesce(i.value,l.value) AS allowed_ips',
                'coalesce(c.email,k.email) AS email',
            ])
            ->from('zclient             c')
            ->innerJoin('zclient        r', 'r.obj_id=c.seller_id')
            ->innerJoin('ref            y', 'y.obj_id=c.type_id')
            ->innerJoin('ref            z', "z.obj_id=c.state_id AND z.name IN ('ok', 'active', 'new')")
            ->leftJoin('contact         k', 'k.obj_id=c.obj_id')
            ->leftJoin('value           t', "t.obj_id=c.obj_id AND t.prop_id=prop_id('client,access:totp_secret')")
            ->leftJoin('value           i', "i.obj_id=c.obj_id AND i.prop_id=prop_id('client,access:allowed_ips')")
            ->leftJoin('value           l', "l.obj_id=c.obj_id AND l.prop_id=prop_id('login_ips:panel')")
            ->leftJoin('client2rolez    e', 'e.client_id=c.obj_id');
    }

    public function andWhere($condition)
    {
        if (!is_array($condition) || $condition[0]) {
            return parent::andWhere($condition);
        }
        foreach (['id', 'username', 'password', 'email', 'active'] as $key) {
            if (isset($condition[$key])) {
                $this->{"where$key"}($condition[$key]);
                unset($condition[$key]);
            }
        }
        if (!empty($condition)) {
            $this->andWhere($condition);
        }

        return $this;
    }

    public function whereId($id)
    {
        return parent::andWhere(['c.obj_id' => $id]);
    }

    public function whereEmail($username)
    {
        return $this->whereUsername($username);
    }

    public function whereUsername($username)
    {
        $userId = (int) $username;
        if ($userId > 0) {
            return $this->whereId($userId);
        }

        return parent::andWhere(['or', 'c.login=:username', 'c.email=:username'], [':username' => $username]);
    }

    public function wherePassword($password)
    {
        return parent::andWhere(
            'check_password(:password,c.password) OR check_password(:password,tmp.value)',
            [':password' => $password]
        )->leftJoin('value tmp', "tmp.obj_id=c.obj_id AND tmp.prop_id=prop_id('client,access:tmp_pwd')");
    }

    public function whereActive($is_active)
    {
        if (is_null($is_active)) {
            return $this;
        }
        return parent::andWhere([$is_active ? 'in' : 'not in', 'z.name', ['ok', 'active']]);
    }
}
