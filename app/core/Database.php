<?php

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            require __DIR__ . '/../config/database.php';

            self::$connection = $pdo;
        }

        return self::$connection;
    }
}