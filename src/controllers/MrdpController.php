<?php
/**
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

namespace hiam\mrdp\controllers;

use Yii;
use yii\helpers\Json;

/**
 * MRDP controller.
 */
class MrdpController extends \yii\web\Controller
{
    /**
     * Implements login from MRDP panel.
     * @param $confirm_data confirmation data
     * @param $goto url to go to on success
     */
    public function actionLogin(array $confirm_data, $goto)
    {
        $url = 'https://api-dev.ahnames.com/verifyConfirmation?' . http_build_query([
            'auth_ip'       => Yii::$app->request->getUserIp(),
            'what'          => 'redirect_hipanel',
            'confirm_data'  => $confirm_data,
        ]);
        $res = Json::decode(file_get_contents($url));
        if (!empty($res['login']) && empty($res['_error'])) {
            $user = $this->user->findIdentity($res['login']);
        } else {
            var_dump($res);die();
        }
        if (!$user) {
            Yii::$app->session->setFlash('error', Yii::t('hiam', 'Failed login.'));
            return $this->goHome();
        }

        $this->user->login($user);

        return $this->redirect($goto);
    }

    public function getUser()
    {
        return Yii::$app->user;
    }
}
