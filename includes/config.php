<?php

/* =========================================================
   HABIBI DATABASE CONFIG
========================================================= */

$dbHost = "localhost";
$dbName = "habibi";
$dbUser = "root";
$dbPass = "";


/* =========================================================
   DATABASE CONNECTION
========================================================= */

try {

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die("خطا در اتصال به پایگاه داده.");

}