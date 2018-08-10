<?php

namespace hiam\mrdp\models;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;

/**
 * Class EmailConfirm is legacy code from old.ahnemes provided email confirmation.
 */
class EmailConfirm extends Model
{
    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $client;

    /**
     * @var integer
     */
    public $id;

    /**
     * @var string The API module name and the method name (in API call names manner) to which the request should be sent.
     * For example: `clientConfirmEmail` or `contactConfirmEmail`
     */
    public $what;

    /**
     * @var string
     */
    public $salt;

    /**
     * @var string
     */
    public $hash;

    /**
     * {@inheritdoc}
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'client', 'email', 'what'], 'required'],
            [['email'], 'email'],
            [['client', 'what', 'salt', 'hash'], 'string'],
            ['what', 'in', 'range' => ['clientConfirmEmail', 'contactConfirmEmail']],
        ];
    }

    /**
     * Send http request to API
     * @return string|bool
     */
    public function confirm()
    {
        $error = null;
        if (!$this->validate()) {
            return implode("\n", $this->getFirstErrors());
        }
        $client = new Client(['baseUrl' => Yii::$app->params['hiapi.url']]);
        $response = $client->createRequest()
            ->setUrl($this->what)
            ->setData(['confirm_data' => $this->attributes])
            ->send();
        $responseData = $response->getData();
        if (array_key_exists('_error', $responseData)) {
            return $responseData['_error'];
        }

        return true;
    }
}

