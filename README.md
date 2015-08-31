SimpleAuthControllerProvider
============================

セッションによるログイン・ログアウトの状態を JSON 形式で返すコントローラープロバイダーです。シングルページアプリケーションの練習のためにつくりました。

インストール
----------

Composer でインストールする場合、次のコードを `package.json` に記載します。

```javascript
{
    "repositories": [
    {
        "type": "package",
        "package": {
            "name": "masakielastic/silex-simpleauth",
            "version": "master",
            "type": "package",
            "source": {
                "url": "https://github.com/masakielastic/silex-simpleauth.git",
                "type": "git",
                "reference": "master"
            },
            "autoload": {
                "psr-4": { "Masakielastic\\Silex\\": "src/" }
            }
        }
    }
    ],
    "require": {
        "masakielastic/silex-simpleauth": "dev-master",
        "silex/silex": "~1.3"
    }
}
```


使い方
-----

Silex のコードは次のようになります。

```php
use Silex\Application;
use Silex\Provider;
use Masakielastic\Silex\SimpleAuthControllerProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app->register(new Provider\SessionServiceProvider());
$app->register(new Provider\DoctrineServiceProvider());

$app['debug'] = true;
$app['db.options'] = [
  'driver'   => 'pdo_sqlite',
  'path'     => __DIR__.'/app.db'
];

$app->before(function (Request $request, Application $app) {
    $app['session']->start();
});

$app->mount('/api', new SimpleAuthControllerProvider);

$app->get('/api/reset', function(Application $app) {
    $app['db']->executeQuery(
      'DROP TABLE IF EXISTS user'
    );

    $app['db']->executeQuery(
      'CREATE TABLE IF NOT EXISTS user('.
      '    id INTEGER PRIMARY KEY AUTOINCREMENT,'.
      '    email TEXT NOT NULL,'.
      '    hash TEXT NOT NULL'.
      ')'
    );

    $app['db']->insert('user', [
        'email' => 'myuser@example.com',
        'hash' => password_hash('mypassword', PASSWORD_DEFAULT)]
    );

    return $app->json(['msg' => 'ok', 'desc' => '初期化しました。']);
});
```

上記のコードでは `/api` のもとにマウントしました。HTTP リクエストとの関係は次のとおりです。

```bash
GET    /api/sessions ログインの状態を確認する
POST   /api/sessions ログイン
DELETE /api/sessions ログアウト
```

レスポンスのフォーマットは JSON です。構成されるプロパティは `login` のみで値は `true` もしくは `false` です。

```javascript
{
    "login": true
}
```

コマンドラインツールの httpie で試してみます。

まずはログインしてみましょう。

```bash
http --session=test GET localhost:3000/api/sessions
http --session=test -f POST localhost:3000/api/sessions email="myuser@example.com" password="mypassword"
http --session=test GET localhost:3000/api/sessions
```

次にログアウトします。

```bash
http --session=test DELETE localhost:3000/api/sessions
http --session=test GET localhost:3000/api/sessions
```

ライセンス
--------

MIT


MIT