<?php
/**
 * Identity and Access Management server providing OAuth2, multi-factor authentication and more
 *
 * @link      https://github.com/hiqdev/hiam
 * @package   hiam
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2014-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiam\mrdp\behaviors;

use hiam\mrdp\models\Identity;
use Yii;
use yii\base\Application;
use yii\base\Event;
use yii\web\Request;

/**
 * SaveReturnUrl behavior.
 *
 * @package hiam\behaviors
 */
class SaveReferralParams extends \yii\base\Behavior
{
    /**
     * @inheritDoc
     */
    public function events(): array
    {
        return [
            Application::EVENT_BEFORE_REQUEST => 'beforeRequest',
            Identity::EVENT_BEFORE_SAVE => 'beforeSave',
        ];
    }

    /**
     * @param Event $event
     */
    public function beforeRequest(Event $event): void
    {
        $request = Yii::$app->getRequest();
        $referralParams = $this->getFromQuery($request) ?: $this->getFromReferrer($request);
        if (!empty($referralParams)) {
            Yii::$app->session->set('referralParams', $referralParams);
        }
    }

    private function getFromQuery(Request $request)
    {
        $params = $request->getQueryParams();

        return $this->getFromArray($params);
    }

    private function getFromReferrer(Request $request)
    {
        parse_str(parse_url($request->getReferrer(), PHP_URL_QUERY), $params);

        return $this->getFromArray($params);
    }

    private function getFromArray(array $params)
    {
        $utmTags = [];
        foreach ($params as $name => $value) {
            if (strstr($name, 'utm_')) {
                $utmTags[$name] = $value;
            }
        }
        return array_filter([
            'referer' => $params['refid'],
            'utmTags' => $utmTags,
        ]);
    }


    public function beforeSave()
    {
        $this->owner->referralParams = \Yii::$app->session->get('referralParams');
    }
}
