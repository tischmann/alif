<?php

namespace Alif;

use Exception;
use stdClass;

class App
{
    public static Db $db;
    public static string $error;
    public static stdClass $config;
    const LOG_FILE  = "error.log";

    static function bootstrap()
    {
        static::$error = '';

        spl_autoload_register(['static', 'loadClass']);

        static::$config = self::getConfig();

        App::$db = new Db(
            static::$config->type,
            static::$config->host,
            static::$config->dbname,
            static::$config->charset,
            static::$config->port,
            static::$config->dbuser,
            static::$config->dbpass
        );
    }

    static function getConfig(): stdClass
    {
        return json_decode(file_get_contents('./config.json'));
    }

    static function loadClass(string $name)
    {
        $name = preg_replace('/[\.|\/]+/', '', $name);
        $name = str_replace('\\', '/', $name);

        $path = "{$name}.php";

        if (!file_exists($path)) {
            die("Error: Class {$name} not found" . PHP_EOL);
        }

        require_once($path);
    }

    static function log(string $text)
    {
        try {
            file_put_contents(
                self::LOG_FILE,
                "[" . date("Y-m-d H:i:s") . "] {$text}" . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        } catch (Exception $ex) {
            die("Error [{$ex->getCode()}]: {$ex->getMessage()}" . PHP_EOL);
        }
    }
}
