<?php

namespace virbo\wsfeeder2;

use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\httpclient\client;
use yii\web\BadRequestHttpException;
use yii\helpers\Json;

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
    }

    public function actFeeder($data = [])
    {
        $mode = $this->mode == 0 ? 'live2.php' : 'sandbox2.php';

        $token = ['token' => $this->_token];

        $data = ArrayHelper::merge($data, $token);

        $request = $this->_client->createRequest()
            ->setUrl('/'.$mode)
            ->addHeaders(['content-type' => 'application/json'])
            ->setContent(Json::encode($data))
            ->setMethod('post')
            ->send();

        if ($request) {
            if ($request->data['error_code'] == 0) {
                return $request;
            } else {
                if ($request->data['error_code'] === 100) {
                    $session = Yii::$app->session;
                    $session['token'] == null;
                } else {
                    throw new BadRequestHttpException('Error '.$request->data['error_code'].' - '.$request->data['error_desc']);
                }
            }
        } else {
            throw new BadRequestHttpException();
        }
    }

    protected function getToken()
    {
        $session = Yii::$app->session;

        if ($session['token'] === null) {
            $act = Json::encode([
                'act' => 'GetToken',
                'username' => $this->username,
                'password' => $this->password
            ]);

            $mode = $this->mode == 0 ? 'live2.php' : 'sandbox2.php';

            $request = $this->_client->createRequest()
                ->setUrl($mode)
                ->addHeaders(['content-type' => 'application/json'])
                ->setContent($act)
                ->setMethod('post')
                ->send();

            if ($request) {
                if ($request->data['error_code'] == 0) {
                    $session['token'] = $request->data['data']['token'];
                } else {
                    throw new BadRequestHttpException('Error '.$request->data['error_code'].' - '.$request->data['error_desc']);
                }
            } else {
                throw new BadRequestHttpException();
            }

            return $this->_token = $session['token'];
        } else {
            return $this->_token = $session['token'];
        }
    }

}
