<?php
session_start();
require_once 'modules/pdo.php';
require_once 'modules/util.php';

if (!isset($_GET['profile_id'])) {
	$_SESSION['error'] = 'The profile you requested is not found.';
	header('Location: index.php');
	return;
}

// FETCH PROFILE
$profile = load_profile($pdo, $_REQUEST['profile_id'], false);
if ($profile === false) {
    $_SESSION['error'] = 'The profile you requested is not found.';
    header('Location: index.php');
    return;
}

// FETCH POSITIONS
$positions = load_positions($pdo, $_REQUEST['profile_id']);

// FETCH EDUCATION
$educations = load_educations($pdo, $_REQUEST['profile_id']);
?>

<!DOCTYPE html>
<html>
<head>
	<title>Vy Ngo Chi - dc8d48be - <?= htmlentities($profile['first_name']).' '.htmlentities($profile['last_name']) ?></title>
	<?php require 'partials/headers.php'; ?>
</head>
<body>
	<?php require 'partials/navbar.php'; ?>
	<div class="container" style="margin: 2em">
		<?=flash()?>
		<div class="card">
            <div class="card-header">
			    <h1>Profile - <?= htmlentities($profile['first_name']).' '.htmlentities($profile['last_name']) ?></h1>
                <?php
                    if ($profile['user_id'] === $_SESSION['user_id']) {
                        echo '<span class="btn-group" style="float: right">';
                        echo '<a class="btn btn-sm btn-warning" href="edit.php?profile_id='.$profile['profile_id'].'">Edit</a>';
                        echo '<a class="btn btn-sm btn-danger" href="delete.php?profile_id='.$profile['profile_id'].'" class="btn btn-danger">Delete</a>';
                        echo '</span>';
                    }
                ?>
            </div>
			<div class="card-body">
				<p class="card-text">First name: <?= htmlentities($profile['first_name'])?></p>
				<p class="card-text">Last name: <?= htmlentities($profile['last_name'])?></p>
				<p class="card-text">Email: <?= htmlentities($profile['email'])?></p>
				<p class="card-text">Headline: <?= htmlentities($profile['headline'])?></p>
				<p class="card-text">Summary: <?= htmlentities($profile['summary'])?></p>

                <h4>Education:</h4>
                <?php
                if (count($educations) == 0) {
                    echo "<p>No education yet</p>";
                } else {
                    echo "<ul>";
                    foreach ($educations as $education) {
                        echo "<li>";
                        echo $education['year'];
                        echo " - ";
                        echo $education['name'];
                        echo "</li>";
                    }
                    echo "</ul>";
                }
                ?>

                <h4>Positions:</h4>
                <?php
					if (count($positions) == 0) {
						echo "<p>No position yet</p>";
					} else {
						echo "<ul>";
						foreach ($positions as $position) {
							echo "<li>";
							echo $position['year'];
							echo " - ";
							echo $position['description'];
							echo "</li>";
						}
						echo "</ul>";
					}
				?>
			</div>
		</div>
		<a class="btn btn-primary" href="index.php" style="margin: 1em">Done</a>
	</div>
</body>
</html>