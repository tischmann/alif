<?php

namespace Alif;

use Exception;
use PDO;

class Db
{
    public PDO $pdo;
    public string $type = 'mysql';
    public string $host = 'localhost';
    public string $dbname = '';
    public string $charset = 'utf8';
    public string $port = '3306';
    public string $dbuser = '';
    public string $dbpass = '';
    public array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => true,
        PDO::ATTR_PERSISTENT => false
    ];

    function __construct(
        string $type = 'mysql',
        string $host = 'localhost',
        string $dbname = '',
        string $charset = 'utf8',
        string $port = '3306',
        string $dbuser = '',
        string $dbpass = ''
    ) {
        $this->type = $type;
        $this->host = $host;
        $this->dbname = $dbname;
        $this->charset = $charset;
        $this->port = $port;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->connect();
    }

    function connect()
    {
        try {
            $dsn = "{$this->type}:host={$this->host}"
                . ";dbname={$this->dbname}"
                . ";charset={$this->charset}"
                . ";port={$this->port}";
            $this->pdo = new PDO(
                $dsn,
                $this->dbuser,
                $this->dbpass,
                $this->options
            );
        } catch (Exception $e) {
            die("DB error [{$e->getCode()}]: {$e->getMessage()}" . PHP_EOL);
        }
    }

    function execute(string $sql, array $args = [])
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $result = $statement->execute($args);
        } catch (Exception $e) {
            die("SQL error [{$e->getCode()}]: {$e->getMessage()}" . PHP_EOL);
        }

        return $result;
    }

    function select(string $sql, array $args = []): array
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($args);
            $result = $statement->fetchAll();
        } catch (Exception $e) {
            die("SQL error [{$e->getCode()}]: {$e->getMessage()}" . PHP_EOL);
        }

        return $result;
    }
}
