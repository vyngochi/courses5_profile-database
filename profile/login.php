<?php
session_start();
require_once 'modules/pdo.php';
require_once 'modules/util.php';

if (isset($_SESSION['user_id'])) {
	$_SESSION['success'] = 'You are already logged in.';
	header('Location: index.php');
	return;
}

if (isset($_POST['email']) && isset($_POST['password'])) {
	$salt = 'XyZzy12*_';
	$check = hash('md5', $salt.$_POST['password']);
	$query = $pdo->prepare("SELECT user_id, name FROM users WHERE email = :email AND password = :password");
	$query->execute(array(':email' => $_POST['email'], ':password' => $check));
	$user = $query->fetch(PDO::FETCH_ASSOC);

	if ($user !== false) {
		$_SESSION['name'] = $user['name'];
		$_SESSION['user_id'] = $user['user_id'];
		$_SESSION['success'] = "You are now logged in.";
		header('Location: index.php');
		return;
	} else {
		$_SESSION['error'] = "Your email and password are not valid.";
		header('Location: index.php');
		return;
	}
} else {
	header('Location: index.php');
	return;
}