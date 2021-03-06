<?php

namespace Wakeup\Mysql;

use PDO;

class Mysql
{
    private $dbHost = '';
    private $dbName = '';
    private $dbUser = '';
    private $dbPassword = '';
    private $connection;

    public function __construct()
    {
        $this->getWordpressDatabaseInformation();
        $this->sqlConnect();
    }

    private function getWordpressDatabaseInformation()
    {
        if (empty($_SERVER['DOCUMENT_ROOT'])) {
            $file = fopen($_ENV['DOCUMENT_ROOT'] . $_ENV['WORDPRESS_CONFIG_PATH'], 'r');
        } else {
            $file = fopen($_SERVER['DOCUMENT_ROOT'] . $_ENV['WORDPRESS_CONFIG_PATH'], 'r');
        }

        if (!empty($file)) {
            while (!feof($file)) {
                $line = (string) fgets($file);

                if (strpos($line, 'DB_NAME')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbName = $targetValue[1];
                }

                if (strpos($line, 'DB_USER')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbUser = $targetValue[1];
                }

                if (strpos($line, 'DB_PASSWORD')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbPassword = $targetValue[1];
                }

                if (strpos($line, 'DB_HOST')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbHost = $targetValue[1];
                }
            }
            fclose($file);

            $this->dbHost = $dbHost;
            $this->dbName = $dbName;
            $this->dbUser = $dbUser;
            $this->dbPassword = $dbPassword;
        }
    }

    private function sqlConnect()
    {
        $this->connection = new PDO('mysql: host = ' . $this->dbHost . '; dbname = ' . $this->dbName . '; charset=utf8', $this->dbUser, $this->dbPassword);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->connection->exec('use ' . $this->dbName);
    }
    
    public function querySelect($sql, $prepareParam)
    {
        $query = $this->connection->prepare($sql);
        foreach ($prepareParam as $paramKey => $paramValue) {
            if ($paramValue === null) {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_NULL);
            } else if (gettype($paramValue) === 'double') {
                $query->bindValue($paramKey, strval($paramValue), PDO::PARAM_STR);
            } else if (gettype($paramValue) === 'boolean') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'string' && ($paramValue == 'true' || $paramValue == 'false')) {
                $paramValue = $paramValue === 'true'? true: false;
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'integer') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
            } else {
                $query->bindValue($paramKey, strval($paramValue), PDO::PARAM_STR);
            }
        }
        $query->execute();
        $result = $query->fetchALL(PDO::FETCH_ASSOC);

        return $result;
    }

    public function query($sql, $prepareParam)
    {
        $query = $this->connection->prepare($sql);
        foreach ($prepareParam as $paramKey => $paramValue) {
            if ($paramValue === null) {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_NULL);
            } else if (gettype($paramValue) === 'double') {
                $query->bindValue($paramKey, strval($paramValue), PDO::PARAM_STR);
            } else if (gettype($paramValue) === 'boolean') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'string' && ($paramValue == 'true' || $paramValue == 'false')) {
                $paramValue = $paramValue === 'true'? true: false;
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'integer') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
            } else {
                $query->bindValue($paramKey, strval($paramValue), PDO::PARAM_STR);
            }
        }
        $result = $query->execute();

        return $result;
    }

    public function getInfo()
    {
        return $this->dbHost . ' dbname = ' . $this->dbName;
    }

    public function insert($sql, $prepareParam)
    {
        $query = $this->connection->prepare($sql);
        foreach ($prepareParam as $paramKey => $paramValue) {
            if (gettype($paramValue) === 'boolean') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'string' && ($paramValue == 'true' || $paramValue == 'false')) {
                $paramValue = $paramValue === 'true'? true: false;
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'integer') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
            } else {
                $query->bindValue($paramKey, $paramValue);
            }
        }
        $query->execute();
        $insertId = $this->connection->lastInsertId();

        return $insertId;
    }
}