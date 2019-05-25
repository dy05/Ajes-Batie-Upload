<?php
session_start();
if(isset($_SESSION['success'])) {
    $success = true;
    unset($_SESSION['success']);
    unset($_POST);
}else{
    $success = false;
}
$errors = [];
$posts = [];
$users = [];
function db(){
    // try permet d'essayer et de ne pas etre bloque si la connexion ne marche pas et de recuperer les erreurs de ce pourquoi cela n'a pas ete un succes
    try {
        $isOk = true;
        $host_name = 'localhost';
        $db_name = 'ajesupload';
        $db_user = 'root';
        $db_pass = '@dyos237';
        // connexion en pdo, on choisit le host, la base de donneess, un username et le mot de passe de redington
        $db = new PDO('mysql:host=' . $host_name . ';dbname=' . $db_name . ';charset=UTF8;', $db_user, $db_pass);
        // On precise quel genre de capture d'erreur on veux
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        // permet de definir le fetch, la recuperation des objects issue de la base de donnees en php
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        // afiiche l'erreur de connexion
        $isOk = false;
        echo $e->getMessage();
    }
    return $isOk ? $db : null;
}

$db = db();
if($db != null) {
    // on prepare la requete
    $query = $db->prepare('SELECT * FROM photos');
    $query->execute();
    $users = $query->fetchAll();
}



if (!empty($_POST)){
    $posts = $_POST;
    if (empty($_POST['noms'])) {
        $errors['noms'] = "Veuillez entrer un nom";
    }else{
        $noms = htmlspecialchars($_POST['noms']);
    }
    if (empty($_POST['prenoms'])) {
        $errors['prenoms'] = "Veuillez entrer un prenom";
    }else{
        $prenoms = htmlspecialchars($_POST['prenoms']);
    }
    if (empty($_POST['poste'])) {
        $errors['poste'] = "Veuillez entrer un poste";
    }else{
        $poste = htmlspecialchars($_POST['poste']);
    }
    if(!empty($_FILES) && isset($_FILES['photo']) && !empty($_FILES['photo'])){
        $fileupload = $_FILES['photo'];
        $file = $fileupload['tmp_name'];
        $filename = explode('.', $fileupload['name']);
        $image_ext = strtolower(end($filename));
        if (!in_array($image_ext, array('jpg', 'jpeg', 'png'))) {
            $errors['photo'] = "Veuillez saisir une image valide";
        }
    }else{
        $errors['photo'] = "Veuillez inserer une photo";
    }
    if($db != null && empty($errors)) {
        $filename = ucwords($noms).'_'.ucwords($prenoms).' - '.ucwords($poste).'.jpg';
        if(!move_uploaded_file($file,'photos/'.$filename)){
            $errors['message'] = "Une erreur inatendue s'est produite";
        }
        if(empty($errors)){
            // on prepare la requete
            $query = $db->prepare('INSERT INTO photos (photo, noms, prenoms, poste) VALUES (:photo, :noms, :prenoms, :poste)');
            $query->bindParam('photo', $filename);
            $query->bindParam('noms', $noms);
            $query->bindParam('prenoms', $prenoms);
            $query->bindParam('poste', $poste);
            if($query->execute()) {
                $_SESSION['success'] = true;
                header('Location:index.php');
            }
        }
    }
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Ajes Upload</title>
<!--    <link rel="stylesheet" href="css/bootstrap.min.css">-->
<!--    <script src="js/bootstrap.min.js"></script>-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        .alert{
            position: absolute;
            top: 0;
            margin-top: 40px;
            right: 10px;
            z-index: 1;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-nav bg-secondary text-white">
    <div class="navbar-brand">
        <h2>Ajes Batie - Douala</h2>
    </div>
</nav>
<div class="container pt-2">
    <h4 style="text-align: center;">Ce site consiste a faire l'ajout des photos et modifier le nom directement !</h4>
    <div class="row mt-5">
        <div class="col-md-6 float-right">
            <div class="card">
                <div class="card-body">
                    <form class="form-group" action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            <input class="form-control <?= isset($errors['photo']) ? 'is-invalid' : ''; ?>" type="file" name="photo" required>
                            <?php if (isset($errors['photo'])): ?>
                            <span class="invalid-feedback" role="alert">
                                <strong><?= $errors['photo']; ?></strong>
                            </span>
                            <?php endif; ?>
                       </div>
                        <div class="col-md-12 mt-3">
                            <label for="noms">Noms</label>
                            <input class="form-control <?= isset($errors['noms']) ? 'is-invalid' : ''; ?>" id="noms" type="text" name="noms" value="<?= isset($posts['noms']) ? $posts['noms'] :  null; ?>" required>
                            <?php if (isset($errors['noms'])): ?>
                                <span class="invalid-feedback" role="alert">
                                    <strong><?= $errors['noms']; ?></strong>
                                </span>
                            <?php endif; ?>
                       </div>
                        <div class="col-md-12 mt-3">
                            <label for="prenoms">Prenoms</label>
                            <input class="form-control <?= isset($errors['prenoms']) ? 'is-invalid' : ''; ?>" id="prenoms" type="text" name="prenoms" value="<?= isset($posts['prenoms']) ? $posts['prenoms'] :  null; ?>" required>
                            <?php if (isset($errors['prenoms'])): ?>
                                <span class="invalid-feedback" role="alert">
                                    <strong><?= $errors['prenoms']; ?></strong>
                                </span>
                            <?php endif; ?>
                       </div>
                        <div class="col-md-12 mt-3">
                            <label for="poste">Poste</label>
                            <input class="form-control <?= isset($errors['poste']) ? 'is-invalid' : ''; ?>" id="poste" type="text" name="poste" value="<?= isset($posts['poste']) ? $posts['poste'] :  'Membre'; ?>" required>
                            <?php if (isset($errors['poste'])): ?>
                                <span class="invalid-feedback" role="alert">
                                    <strong><?= $errors['poste']; ?></strong>
                                </span>
                            <?php endif; ?>
                       </div>
                        <div class="col-md-12 mt-4">
                            <div class="form-row">
                                <button class="btn btn-primary" type="submit">Ajouter</button>
        <!--                        <button class="btn btn-danger" type="reset">Renitialiser</button>-->
                            </div>
                       </div>

                    </div>
                </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Listes des adherents presents
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 border-bottom pb-3 mb-4">
                            <a class="btn btn-secondary" target="_blank" href="zipper.php">Zipper tout ca</a>
                            <?php if(file_exists('zipper.zip')): ?>
                                <a class="btn btn-outline-success" href="zipper.zip">Telecharger le zip</a>
                            <?php endif; ?>
                        </div>
                        <?php foreach ($users as $user): ?>
                            <div class="col-md-12">
                                <div class="card border-left-0 border-right-0 border-top-0 mb-3 pb-0">
                                    <img src="photos/<?= $user->photo; ?>" alt="<?= $user->noms.' '.$user->prenoms; ?>" class="card-img" style="max-width: 200px;max-height: 200px;">
                                    <div class="card-body row">
                                        <div class="col-md-6">
                                        <p>Noms: <strong><?= $user->prenoms; ?></strong></p>
                                        <p>Prenoms: <strong><?= $user->prenoms; ?></strong></p>
                                        <em>Poste: <strong><?= $user->poste; ?></strong></em>
                                        </div>
                                        <dic class="col-md-6">
                                            <p>Nom de l'image: <br/><em style="color:red;"><?= $user->photo; ?></em></p>
                                        </dic>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
<?php if($success || isset($errors['message'])): ?>
<div id="alert" class="alert <?= $success ? 'alert-success bg-success' : 'alert-danger bg-danger'; ?> text-white justify-content-center">
    <?php if($success): ?>
        <p>Ajoute avec succes !</p>
    <?php else: ?>
    <p><?= $errors['message']; ?></p>
    <?php endif; ?>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
<!--<script src="js/jquery.js"></script>-->
<script>
    window.onload = function () {
        // history.replaceState(null);
        //Remplacer l'history lorsque les alerts s'affichent.
        setTimeout(function () {
            var elemAlert = $('#alert')
            elemAlert.animate({'top': '-50px', 'opacity': 0}, 'slow')
            setTimeout(function () {
                elemAlert.css('display', 'none');
            }, 500);
        }, 2000);
    }
</script>
<?php endif; ?>

</body>
</html>
