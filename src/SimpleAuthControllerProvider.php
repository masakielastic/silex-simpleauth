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
            return $app->json(['login' => (bool) $app['session']->get($this->prefix.'.login')]);
        });

        $controllers->post('/sessions', function(Application $app, Request $request) {

            $email = $request->get('email');
            $password = $request->get('password');

            if ($email === null) {
                return $app->json(['login' => false, 'desc' => 'email が送信されていません。'], 400);
            }

            if ($password === null) {
                return $app->json(['login' => false, 'desc' => 'password が送信されていません。'], 400);
            }

            $hash = $app['db']->fetchColumn('SELECT hash FROM user WHERE email = ?', [$email], 0);

            if ($hash === false || !password_verify($password, $hash)) {
                return $app->json(['login' => false, 'desc' => 'メールアドレスもしくはパスワードが正しくありません。']);
            }

            $app['session']->migrate(true);
            $app['session']->set($this->prefix.'.login', true);

            return $app->json(['login' => (bool)  $app['session']->get($this->prefix.'.login', true)]);
        });

        $controllers->delete('/sessions', function (Application $app, Request $request) {

            $app['session']->invalidate();

            return $app->json(['login' => (bool) $app['session']->get($this->prefix.'.login')]);
        });

        return $controllers;
    }
}