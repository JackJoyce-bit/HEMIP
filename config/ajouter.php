<?php

session_start();

require_once "connexion.php";

// Accès réservé aux SuperAdmin connectés (rôle relu en base)
$roleReel = false;

if (!empty($_SESSION['admin_connecte']) && !empty($_SESSION['idAdmin'])) {
    $r = $connexion->prepare("SELECT role FROM administrateur WHERE idAdmin = ?");
    $r->execute([(int) $_SESSION['idAdmin']]);
    $roleReel = $r->fetchColumn();
}

if ($roleReel !== 'SuperAdmin') {
    header("Location: login.php");
    exit();
}

$rolesAutorises = ['Administrateur', 'SuperAdmin'];

$message = "";
$succes = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';
    $role = $_POST['role'] ?? 'Administrateur';

    if (
        empty($nom) ||
        empty($prenom) ||
        empty($email) ||
        empty($motdepasse)
    ) {
        $message = "Veuillez remplir tous les champs.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Adresse email invalide.";

    } elseif (!in_array($role, $rolesAutorises, true)) {

        $message = "Rôle invalide.";

    } elseif (strlen($motdepasse) < 6) {

        $message = "Le mot de passe doit contenir au moins 6 caractères.";

    } else {

        // Vérifier si l'email existe déjà
        $verification = $connexion->prepare(
            "SELECT idAdmin FROM administrateur WHERE email = ?"
        );

        $verification->execute([$email]);

        if ($verification->fetch()) {

            $message = "Cet email est déjà utilisé.";

        } else {

            // Cryptage du mot de passe
            $motdepasseHash = password_hash(
                $motdepasse,
                PASSWORD_DEFAULT
            );

            // Enregistrement de l'administrateur
            $requete = $connexion->prepare(
                "INSERT INTO administrateur
                (nom, prenom, email, motdepasse, role)
                VALUES (?, ?, ?, ?, ?)"
            );

            $requete->execute([
                $nom,
                $prenom,
                $email,
                $motdepasseHash,
                $role
            ]);

            $succes = "L'administrateur a été ajouté avec succès.";
            $_POST = [];
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css"
        rel="stylesheet"
    >
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/inscription.css">
     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg" style="border-radius: 20px;">

    <title>Ajouter un administrateur - HEMIP</title>

    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, Helvetica, sans-serif;
        }

        /* =========================
           NAVIGATION
        ========================= */

        .navbar {
            height: 68px;
            width: 100%;

            background: #101a23;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 30px;

            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo img {
            width: 43px;
            height: 43px;

            border-radius: 50%;
            object-fit: cover;

            background: white;
        }

        .logo span {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.3px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 38px;
        }

        .menu a {
            color: white;
            text-decoration: none;

            font-size: 14px;

            transition: 0.3s;
        }

        .menu a:hover {
            color: #b6a5ff;
        }

        .menu .active {
            color: #b6a5ff;
        }

        /* =========================
           CONTENU
        ========================= */

        .page {
            min-height: calc(100vh - 68px);

            display: flex;
            justify-content: center;
            align-items: center;

            padding: 45px 20px;
        }

        /* =========================
           CARTE FORMULAIRE
        ========================= */

        .form-card {
            width: 390px;

            background: rgba(238, 241, 249, 0.94);

            padding: 30px 25px 35px;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.18);

            backdrop-filter: blur(3px);

            border-radius: 20px;
        }

        .form-title {
            text-align: center;

            color: #101a23;

            font-size: 24px;
            font-weight: 600;

            margin-bottom: 7px;
        }

        .form-subtitle {
            text-align: center;

            color: #555;

            font-size: 13px;

            margin-bottom: 25px;
        }

        /* =========================
           MESSAGE ERREUR
        ========================= */

        .message {
            background: #ffe5e5;

            color: #b00020;

            border-left: 4px solid #b00020;

            padding: 11px 12px;

            margin-bottom: 20px;

            font-size: 13px;
        }

        .message.succes {
            background: #e6f6ec;
            color: #14683d;
            border-left-color: #14683d;
        }

        .select-group {
            margin-bottom: 24px;
        }

        .select-group label {
            display: block;
            font-size: 11px;
            color: #4c2de0;
            margin-bottom: 4px;
        }

        .select-group select {
            width: 100%;
            border: none;
            border-bottom: 1px solid #7357e8;
            background: transparent;
            padding: 9px 4px;
            font-size: 14px;
            outline: none;
        }

        /* =========================
           CHAMPS
        ========================= */

        .input-group {
            position: relative;

            margin-bottom: 24px;
        }

        .input-group input {
            width: 100%;

            border: none;
            outline: none;

            background: transparent;

            padding: 9px 4px 10px;

            font-size: 14px;

            color: #111;

            border-bottom: 1px solid #7357e8;

            transition: 0.3s;
        }

        .input-group label {
            position: absolute;

            left: 4px;
            top: 9px;

            font-size: 14px;

            color: #172060;

            pointer-events: none;

            transition: 0.25s;
        }

        .input-group input:focus {
            border-bottom: 2px solid #4c2de0;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: -13px;

            font-size: 11px;

            color: #4c2de0;
        }

        /* =========================
           BOUTON
        ========================= */

        .btn {
            width: 100%;

            border: none;

            padding: 13px;

            margin-top: 5px;

            border-radius: 25px;

            background: #101a23;

            color: white;

            font-size: 15px;

            cursor: pointer;

            box-shadow:
                0 5px 12px rgba(0, 0, 0, 0.18);

            transition: 0.3s;
        }

        .btn:hover {
            background: #7357e8;

            transform: translateY(-1px);
        }

        /* =========================
           LIEN RETOUR
        ========================= */

        .back {
            display: block;

            text-align: center;

            margin-top: 20px;

            color: #172060;

            text-decoration: none;

            font-size: 13px;
        }

        .back:hover {
            color: #7357e8;
            text-decoration: underline;
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 800px) {

            .menu {
                gap: 15px;
            }

            .menu a {
                font-size: 12px;
            }

            .navbar {
                padding: 0 15px;
            }
        }

        @media (max-width: 600px) {

            .navbar {
                height: auto;

                padding: 12px 18px;

                flex-direction: column;
                gap: 12px;
            }

            .menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .page {
                min-height: calc(100vh - 110px);

                padding: 30px 15px;
            }

            .form-card {
                width: 100%;
                max-width: 390px;
            }
        }

    </style>

</head>

<body>

    <!-- =========================
         BARRE DE NAVIGATION
    ========================= -->

    <header class="navbar">

        <div class="logo">

            <!-- Mets ici ton logo HEMIP -->
            <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">

            <span>HEMIP</span>

        </div>

        <nav class="menu">

            <a href="index.php">Accueil</a>

            <a href="apropos.php">À propos</a>

            <a href="formation.php">Formation</a>

            <a href="inscription.php">Inscription</a>

            <a href="actualite.php">Actualité</a>

            <a href="contact.php">Contact</a>

            <a href="test.php" class="active">♙</a>

        </nav>

    </header>


    <!-- =========================
         FORMULAIRE
    ========================= -->

    <main class="page">

        <div class="form-card">

            <h1 class="form-title">
                Ajouter un administrateur
            </h1>

            <p class="form-subtitle">
                Créez un nouveau compte administrateur
            </p>


            <?php if (!empty($message)): ?>

                <div class="message">
                    <?= htmlspecialchars($message) ?>
                </div>

            <?php endif; ?>

            <?php if (!empty($succes)): ?>

                <div class="message succes">
                    <?= htmlspecialchars($succes) ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <!-- NOM -->

                <div class="input-group">

                    <input
                        type="text"
                        name="nom"
                        id="nom"
                        placeholder=" "
                        value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                        required
                    >

                    <label for="nom">
                        Nom
                    </label>

                </div>


                <!-- PRENOM -->

                <div class="input-group">

                    <input
                        type="text"
                        name="prenom"
                        id="prenom"
                        placeholder=" "
                        value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                        required
                    >

                    <label for="prenom">
                        Prénom
                    </label>

                </div>


                <!-- EMAIL -->

                <div class="input-group">

                    <input
                        type="email"
                        name="email"
                        id="email"
                        placeholder=" "
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        required
                    >

                    <label for="email">
                        Email
                    </label>

                </div>


                <!-- RÔLE -->

                <div class="select-group">

                    <label for="role">Rôle</label>

                    <select name="role" id="role" required>
                        <option value="Administrateur">Administrateur</option>
                        <option value="SuperAdmin">SuperAdmin</option>
                    </select>

                </div>


                <!-- MOT DE PASSE -->

                <div class="input-group">

                    <input
                        type="password"
                        name="motdepasse"
                        id="motdepasse"
                        placeholder=" "
                        minlength="6"
                        required
                    >

                    <label for="motdepasse">
                        Mot de passe
                    </label>

                </div>


                <!-- BOUTON -->

                <button
                    type="submit"
                    class="btn"
                >
                    Ajouter l'administrateur
                </button>

            </form>

            <a href="administrateurs.php" class="back">
                ← Retour à la liste des administrateurs
            </a>

        </div>

    </main>

</body>

</html>