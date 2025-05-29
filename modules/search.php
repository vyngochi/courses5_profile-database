<?php
/**
 * Created by PhpStorm.
 * User: mathieuleger-dalcourt
 * Date: 17-11-23
 * Time: 15:40
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Access denied. Please login first.";
    header('Location: index.php');
    return;
}

require_once 'pdo.php';

header("Content-type: application/json; charset: utf-8");

$term = $_GET['term'];
$query = $pdo->prepare("SELECT name FROM institutions WHERE name LIKE :prefix");
$query->execute(array(':prefix' => $term."%"));

$retval = array();
while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
    $retval[] = $row['name'];
}

echo(json_encode($retval, JSON_PRETTY_PRINT));