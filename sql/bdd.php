<?php

$host = "localhost";
$db='cabinet_medical';
$user ='root';
$pass ='';
$port = '3306';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE =>\PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE =>\PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

$dsn = "mysql:host=$host;dbname=$db;port=$port;charset=$charset";
$pdo = new PDO($dsn, $user, $pass, $options);

try{
    $pdo=new PDO($dsn, $user, $pass, $options);
}catch(PDOException $e){
    echo $e->getMessage();
}
?>