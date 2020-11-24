<?php

namespace Wakeup\Mysql;

use \PDO;

require __DIR__ . '/vendor/autoload.php';
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

class Mysql
{
    private $dbHost = '';
    private $dbName = '';
    private $dbUser = '';
    private $dbPassword = '';
    private $connection;

    public function __construct()
    {
        $this->getWordpressDatabaseInfomation();
        $this->sqlConnect();
    }

    private function getWordpressDatabaseInfomation()
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
    }
    
    public function querySelect($sql, $prepareParam)
    {
        $query = $this->connection->prepare($sql);
        foreach ($prepareParam as $paramKey => $paramValue) {
            if (gettype($paramValue) === 'string' && ($paramValue == 'true' || $paramValue == 'false')) {
                $paramValue = $paramValue === 'true'? true: false;
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else if (gettype($paramValue) === 'integer') {
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_INT);
            } else {
                $query->bindValue($paramKey, $paramValue);
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
            if (gettype($paramValue) === 'string' && ($paramValue == 'true' || $paramValue == 'false')) {
                $paramValue = $paramValue === 'true'? true: false;
                $query->bindValue($paramKey, $paramValue, PDO::PARAM_BOOL);
            } else {
                $query->bindValue($paramKey, $paramValue);
            }
        }
        $result = $query->execute();

        return $result;
    }

    public function test()
    {
        $sql = "SELECT * FROM `$this->dbName`.`wp_users` WHERE `ID` <> :id";
        $prepareParam = [];
        $prepareParam['id'] = 0;
        
        return $this->querySelect($sql, $prepareParam);
    }
}
