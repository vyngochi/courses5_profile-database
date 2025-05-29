<?php
session_start();
require_once 'modules/pdo.php';
require_once 'modules/util.php';

// VALIDATE THE PARAM OF THE REQUEST
if (!isset($_SESSION['user_id'])) {
	$_SESSION['error'] = "Access denied. Please login first.";
	header('Location: index.php');
	return;
}

if (!isset($_REQUEST['profile_id'])) {
    $_SESSION['error'] = 'The profile you requested is not found.';
    header('Location: index.php');
    return;
}

// FETCH PROFILE
$profile = load_profile($pdo, $_REQUEST['profile_id'], true);
if ($profile === false) {
    $_SESSION['error'] = 'The profile you requested is not found or your access is denied.';
    header('Location: index.php');
    return;
}

// POST CONTROLLER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	// VALIDATION
	$message = validate_profile();
	if (is_string($message)) {
		$_SESSION['error'] = $message;
		header("Location: edit.php?profile_id=".$_POST['profile_id']);
		return;
	}

	$message = validate_pos();
	if (is_string($message)) {
		$_SESSION['error'] = $message;
		header("Location: add.php");
		return;
	}

    $message = validate_edu();
    if (is_string($message)) {
        $_SESSION['error'] = $message;
        header("Location: add.php");
        return;
    }

	// UPDATE PROFILE
	$query = $pdo->prepare("UPDATE profiles SET first_name=:first_name, last_name=:last_name, email=:email, headline=:headline, summary=:summary WHERE profile_id=:profile_id");
	$query->execute(array(
		':profile_id' => $_POST['profile_id'],
		':first_name' => $_POST['first_name'],
		':last_name' => $_POST['last_name'],
		':email' => $_POST['email'],
		':headline' => $_POST['headline'],
		':summary' => $_POST['summary']
	));

	// CLEAR THE PREVIOUS POSITIONS
	$query = $pdo->prepare("DELETE FROM positions WHERE profile_id = :profile_id");
	$query->execute(array(':profile_id' => $_REQUEST['profile_id']));

    //	DELETE PREVIOUS EDUCATIONS
    $query = $pdo->prepare("DELETE FROM educations WHERE profile_id = :profile_id");
    $query->execute(array(':profile_id' => $_REQUEST['profile_id']));

	// INSERT  NEW POSITIONS
    $profile_id = $_POST['profile_id'];
    $insert_res = insert_positions($pdo, $profile_id);
    if ($insert_res !== true){
        $_SESSION['error'] = $insert_res;
        header('Location: add.php');
        return;
    }

    // INSERT NEW EDUCATION
    $insert_res = insert_educations($pdo, $profile_id);
    if ($insert_res !== true){
        $_SESSION['error'] = $insert_res;
        header('Location: add.php');
        return;
    }

	// REDIRECT
	$_SESSION['success'] = 'Profile edited';
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
	<title>Edit a profile</title>
	<?php require 'partials/headers.php'; ?>
	<script type="text/javascript">var countPos=<?=count($positions)?>;</script>
	<script type="text/javascript">var countEdu=<?=count($educations)?>;</script>
</head>
<body>
	<?php require 'partials/navbar.php'; ?>
	<div class="container">
		<?=flash()?>

		<div class="card" style="margin-top: 1em">
			<h1 class="card-header">Edit a profile - <?= htmlentities($profile['first_name']).' '.htmlentities($profile['last_name']) ?></h1>
			<div class="card-body">
				<form action="edit.php?profile_id=<?=$profile['profile_id']?>" method="POST">
					<div class="form-row">
						<div class="form-group col-md-6">
							<label for="first_name">First name</label>
							<input class="form-control" type="text" name="first_name" placeholder="Enter your first name" value="<?= htmlentities($profile['first_name'])?>">
						</div>
						<div class="form-group col-md-6">
							<label for="last_name">Last name</label>
							<input class="form-control" type="text" name="last_name" placeholder="Enter your last name" value="<?= htmlentities($profile['last_name'])?>">
						</div>
					</div>
					<div class="form-group">
						<label for="email">Email</label>
						<input class="form-control" type="email" name="email" placeholder="Enter your email" value="<?= htmlentities($profile['email'])?>">
					</div>
					<div class="form-group">
						<label for="headline">Headline</label>
						<input class="form-control" type="text" name="headline" placeholder="Enter your headline" value="<?= htmlentities($profile['headline'])?>">
					</div>
					<div class="form-group">
						<label for="summary">Summary</label>
						<textarea class="form-control" name="summary" placeholder="Enter a brief summary" ><?= htmlentities($profile['summary'])?></textarea>
					</div>

<!--                EDUCATION-->
                    <div class="container-fluid" style="margin-top: 1em; margin-bottom: 1em">
                        <h4>Education:</h4>
                        <div id="education_fields">
                            <?php
                            if (count($educations)>0) {
                                foreach ($educations as $education) {
                                    echo '<div id="edu'.$education['rank'].'" class="card" style="margin-bottom: 1em;">';
                                    echo '<div class="card-body">';
                                    echo '<div class="form-group row">';
                                    echo '<label class="col-form-label col-sm-2" for="edu_year">Year: </label>';
                                    echo '<div class="col-sm-4">';
                                    echo '<input class="form-control" type="number" name="edu_year'.$education['rank'].'" min="1900" max="2020" value="'.htmlentities($education['year']).'">';
                                    echo '</div>';
                                    echo '<div class="col-sm-6">';
                                    echo '<button type="button" onclick="$(\'#edu'.$education['rank'].'\').remove();return;" style="float: right;" class="btn btn-danger btn-sm">-</button>';
                                    echo '</div></div>';
                                    echo '<div class="form-group">';
                                    echo '<label for="edu_desc">Institution</label>';
                                    echo '<input type="text" class="form-control school" name="edu_desc'.$education['rank'].'" placeholder="Enter your school or institution." value="'.htmlentities($education['name']).'">';
                                    echo '</div></div></div>';
                                }
                            }
                            ?>
                        </div>
                        <div><button id="add_edu" class="btn btn-success btn-sm" style="margin-top: 1em; margin-bottom: 1em">+</button></div>
                    </div>

<!--                    POSITIONS-->
                    <div class="container-fluid" style="margin-top: 1em; margin-bottom: 1em">
						<h4>Positions:</h4>
						<div id="position_fields">
							<?php 
							if (count($positions)>0) {
								foreach ($positions as $position) {
									echo '<div id="pos'.$position['rank'].'" class="card" style="margin-bottom: 1em;">';
									echo '<div class="card-body">';
									echo '<div class="form-group row">';
									echo '<label class="col-form-label col-sm-2" for="pos_year">Year: </label>';
									echo '<div class="col-sm-4">';
									echo '<input class="form-control" type="number" name="pos_year'.$position['rank'].'" min="1900" max="2020" value="'.htmlentities($position['year']).'">';
									echo '</div>';
									echo '<div class="col-sm-6">';
									echo '<button type="button" onclick="$(\'#pos'.$position['rank'].'\').remove();return;" style="float: right;" class="btn btn-danger btn-sm">-</button>';
									echo '</div></div>';
									echo '<div class="form-group">';
									echo '<label for="pos_desc">Description</label>';
									echo '<textarea class="form-control" name="pos_desc'.$position['rank'].'" placeholder="Enter a brief description of the position you occupied." >'.htmlentities($position['description']).'</textarea>';
									echo '</div></div></div>';
								}
							}
							
							?>
						</div>
						<div><button id="add_pos" class="btn btn-success btn-sm" style="margin-top: 1em; margin-bottom: 1em">+</button></div>
					</div>
					<input type="hidden" name="profile_id" value="<?=$profile['profile_id']?>">
					<button class="btn btn-primary" type="submit">Edit profile</button>
					<a class="btn btn-secondary" href="edit.php?profile_id=<?=$profile['profile_id']?>">Reset</a>
				</form>
			</div>
		</div>
		<a href="index.php" class="btn btn-secondary" style="margin-top: 1em; margin-bottom: 1em">Return home</a>
	</div>
</body>
</html>