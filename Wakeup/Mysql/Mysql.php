<?php

namespace Wakeup\Mysql;

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
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php', 'r');

        if (!empty($file)) {

            while (!feof($file)) {
                $line = (string) fgets($file);

                if (strpos($line, 'dbName')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbName = $targetValue[1];
                }

                if (strpos($line, 'dbUser')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbUser = $targetValue[1];
                }

                if (strpos($line, 'dbPassword')) {
                    $targetSentence = explode(',', $line);
                    $targetValue = explode('\'', $targetSentence[1]);
                    $dbPassword = $targetValue[1];
                }

                if (strpos($line, 'dbHost')) {
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

    private function test()
    {
        $sql = $this->connection->prepare('SELECT * FROM `wp_user` WHERE `id` != ?');
        $sql->bindValue(1, 0);
        $sql->execute();
        $result = $sql->fetch(PDO::FETCH_ASSOC);

        return $result;
    }
}
