<?php

namespace virbo\wsfeeder2;

use yii\base\Component;
use yii\httpclient\client;
use yii\web\BadRequestHttpException;

/**
 * This is just an example.
 */
class Feeder extends Component
{
    /*
     * @var string endpoint the WS Feeder
     * */
    public $endpoint;

    /*
     * @var string username the WS Feeder
     * */
    public $username;

    /*
     * @var string password the WS Feeder
     * */
    public $password;

    /*
     * @var integer mode the WS Feeder (0 = Live, 1 = Sandbox)
     * */
    public $mode = 0;

    /**
     * @var client instance
     */
    protected $_client;

    /*
     * @var string token the WS Feeder
     * */
    protected $_token = null;

    public function init()
    {
        $this->_client = new Client([
            'baseUrl' => $this->endpoint
        ]);

        $this->getToken();

        return $this->_client;
    }

    public function getToken()
    {
        $act = json_encode([
            'act' => 'GetToken',
            'username' => $this->username,
            'password' => $this->password
        ]);

        $responseWs = $this->_client->createRequest()
            ->setUrl($this->mode)
            ->addHeaders(['content-type' => 'application/json'])
            ->setContent($act)
            ->setMethod('post')
            ->send();

        if ($responseWs->data['error_code'] == 0) {
            $this->_token = $responseWs->data['data']['token'];
        } else {
            throw new BadRequestHttpException();
        }

        return $this->_token;
    }

    public function actFeeder($data)
    {
        $mode = $this->mode == 0 ? 'live2.php' : 'sandbox2.php';

        $request = $this->_client->createRequest()
            ->setUrl('/'.$mode)
            ->addHeaders(['content-type' => 'application/json'])
            ->setContent($data)
            ->setMethod('post')
            ->send();

        return $request;
    }

}
