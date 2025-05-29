<nav class="navbar navbar-light bg-light justify-content-between">
 	<a href="index.php" class="navbar-brand">Profile Database</a>
<?php 
	if (isset($_SESSION['user_id'])) {
	    echo $_SESSION['name'];
		echo '<a class="btn btn-primary my-2 my-sm-0" href="logout.php">Logout</a>';
	} else {
		echo '<form class="form-inline" action="login.php" method="POST">';
		echo '<input class="form-control mr-sm-2" type="email" placeholder="Email" name="email" required>';
		echo '<input class="form-control mr-sm-2" type="password" placeholder="Password" name="password" required>';
		echo '<button class="btn btn-primary my-2 my-sm-0" type="submit">Login</button>';
		echo "</form>";
	}
?>
</nav>