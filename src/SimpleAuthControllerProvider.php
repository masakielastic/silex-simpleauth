<?php

namespace Masakielastic\Silex;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class SimpleAuthControllerProvider implements ControllerProviderInterface
{
    private $prefix = 'simpleAuth';

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/sessions', function(Application $app, Request $request) {

            $msg = $app['session']->get($this->prefix.'.login') ? 'ok' : 'fail';

            return $app->json(['msg' => $msg]);
        });

        $controllers->post('/sessions', function(Application $app, Request $request) {

            $email = $request->get('email');
            $password = $request->get('password');

            if ($email === null) {
                return $app->json(['msg' => 'fail', 'desc' => 'email が送信されていません。'], 400);
            }

            if ($password === null) {
                return $app->json(['msg' => 'fail', 'desc' => 'password が送信されていません。'], 400);
            }

            $hash = $app['db']->fetchColumn('SELECT hash FROM user WHERE email = ?', [$email], 0);

            if ($hash === false || !password_verify($password, $hash)) {
                return $app->json(['msg' => 'fail', 'desc' => 'メールアドレスもしくはパスワードが正しくありません。']);
            }

            $app['session']->migrate(true);
            $app['session']->set($this->prefix.'.login', true);

            return $app->json(['msg' => 'ok']);
        });

        $controllers->delete('/sessions', function (Application $app, Request $request) {

            if (!$app['session']->get($this->prefix.'.login')) {
                return $app->json(['msg' => 'fail']);
            }

            $app['session']->invalidate();

            return $app->json(['msg' => 'ok']);
        });

        return $controllers;
    }
}