<?php

function flash() {
	if ( isset($_SESSION['error']) ) {
	    $flash = '<div class="alert alert-danger">'.$_SESSION['error']."</div>\n";
	    unset($_SESSION['error']);
	    return $flash;
	}
	if ( isset($_SESSION['success']) ) {
	    $flash = '<div class="alert alert-success">'.$_SESSION['success']."</div>\n";
	    unset($_SESSION['success']);
	    return $flash;
	}
}

// -------------------------------------- LOADING --------------------------------------
function load_profile($pdo, $profile_id, $secured=true) {
    if ($secured !== false) {
        $query = $pdo->prepare("SELECT * FROM profiles WHERE profile_id = :profile_id AND user_id = :user_id");
        $query->execute(array(
            ':profile_id' => $_REQUEST['profile_id'],
            ':user_id' => $_SESSION['user_id']
        ));
        $profile = $query->fetch(PDO::FETCH_ASSOC);
    } else {
        $query = $pdo->prepare("SELECT * FROM profiles WHERE profile_id = :profile_id");
        $query->execute(array(':profile_id' => $profile_id));
        $profile = $query->fetch(PDO::FETCH_ASSOC);
    }

    if ($profile === false) {
        return false;
    }

    return $profile;
}

function load_positions($pdo, $profile_id) {
    $query = $pdo->prepare("SELECT * FROM positions WHERE profile_id = :profile_id");
    $query->execute(array(':profile_id' => $profile_id));
    $positions = $query->fetchAll(PDO::FETCH_ASSOC);

    return $positions;
}

function load_educations($pdo, $profile_id) {
    $query = $pdo->prepare("SELECT educations.profile_id, educations.year, educations.rank, institutions.name FROM educations JOIN institutions on educations.institution_id=institutions.institution_id WHERE educations.profile_id=:profile_id");
    $query->execute(array('profile_id' => $profile_id));
    $educations = $query->fetchAll(PDO::FETCH_ASSOC);

    return $educations;
}


// -------------------------------------- VALIDATING --------------------------------------
function validate_profile() {
    if (strlen($_POST['first_name'])<1 || strlen($_POST['last_name'])<1 || strlen($_POST['email'])<1 || strlen($_POST['headline'])<1 || strlen($_POST['summary'])<1) {
        return "All fields are required";
    }

    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }

    return True;
}

function validate_edu() {
    for ($i=1; $i < 11; $i++) {
        if (!isset($_POST['edu_year'.$i])) continue;
        if (!isset($_POST['edu_desc'.$i])) continue;
        if (strlen($_POST['edu_year'.$i])<1 || strlen($_POST['edu_desc'.$i])<1) {
            return "All fields in education are required";
        }
        if (!is_numeric($_POST['edu_year'.$i])) {
            return "Year must be numeric";
        }
        return True;
    }
}

function validate_pos() {
    for ($i=1; $i < 11; $i++) {
        if (!isset($_POST['pos_year'.$i])) continue;
        if (!isset($_POST['pos_desc'.$i])) continue;
        if (strlen($_POST['pos_year'.$i])<1 || strlen($_POST['pos_desc'.$i])<1) {
            return "All fields in position are required";
        }
        if (!is_numeric($_POST['pos_year'.$i])) {
            return "Year must be numeric";
        }
        return True;
    }
}


// -------------------------------------- INSERTIONS ----------------------------------------------------------------------------

function insert_positions($pdo, $profile_id){
    try {
        $rank = 1;
        for ($i=1; $i < 11; $i++) {
            if (!isset($_POST['pos_year' . $i])) continue;
            if (!isset($_POST['pos_desc' . $i])) continue;
            $query = $pdo->prepare("INSERT INTO positions (profile_id, rank, year, description) VALUES(:profile_id, :rank, :year, :desc)");
            $query->execute(array(
                ':profile_id' => $profile_id,
                ':rank' => $rank,
                ':year' => $_POST['pos_year' . $i],
                ':desc' => $_POST['pos_desc' . $i]
            ));
            $rank++;
        }
    } catch (Exception $e) {
            return $e;
        }
    return true;
}

function insert_educations($pdo, $profile_id){
    try {
        $rank = 1;
        for ($i=1; $i < 11; $i++) {
            if (!isset($_POST['edu_year'.$i])) continue;
            if (!isset($_POST['edu_desc'.$i])) continue;

            // look for institutions
            $query = $pdo->prepare("SELECT institution_id FROM institutions WHERE name=:name");
            $query->execute(array(':name' => $_POST['edu_desc'.$i]));
            $row = $query->fetch(PDO::FETCH_ASSOC);

            if ($row === false) {
                $query = $pdo->prepare("INSERT INTO institutions (name) VALUES (:name)");
                $query->execute(array(':name' => $_POST['edu_desc'.$i]));
                $institution_id = $pdo->lastInsertId();
            } else {
                $institution_id = $row['institution_id'];
            }

            // Insert education
            $query = $pdo->prepare("INSERT INTO educations (profile_id, rank, year, institution_id) VALUES(:profile_id, :rank, :year, :institution_id)");
            $query->execute(array(
                ':profile_id' => $profile_id,
                ':rank' => $rank,
                ':year' => $_POST['edu_year' . $i],
                ':institution_id' => $institution_id
            ));
            $rank++;
        }
    } catch (Exception $e) {
        return $e;
    }
    return true;
}

