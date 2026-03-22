<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (isset($_SERVER['APP_ENV'])) {
    $_ENV['APP_ENV'] = $_SERVER['APP_ENV'];
    putenv('APP_ENV=' . $_SERVER['APP_ENV']);
}
if (isset($_SERVER['APP_DEBUG'])) {
    $_ENV['APP_DEBUG'] = $_SERVER['APP_DEBUG'];
    putenv('APP_DEBUG=' . $_SERVER['APP_DEBUG']);
}
if (isset($_SERVER['DATABASE_URL'])) {
    $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'];
    putenv('DATABASE_URL=' . $_SERVER['DATABASE_URL']);
}

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}

if (($_SERVER['APP_ENV'] ?? null) === 'test') {
    $marker = dirname(__DIR__) . '/var/.phpunit_db_ready';

    if (!is_file($marker)) {
        @mkdir(dirname($marker), 0777, true);

        $cmds = [
            'php bin/console doctrine:database:create --env=test --if-not-exists',
            'php bin/console doctrine:migrations:migrate --env=test --no-interaction',
        ];

        foreach ($cmds as $cmd) {
            passthru($cmd, $code);
            if ($code !== 0) {
                fwrite(STDERR, "Command failed: {$cmd}\n");
                exit($code);
            }
        }

        file_put_contents($marker, (string) time());
    }
}
