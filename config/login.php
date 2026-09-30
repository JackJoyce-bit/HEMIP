<?php

session_start();

require_once "connexion.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email'] ?? '');
    $motdepasse = $_POST['motdepasse'] ?? '';

    if (empty($email) || empty($motdepasse)) {

        $message = "Veuillez remplir tous les champs.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Adresse email invalide.";

    } else {

        // Rechercher l'administrateur
        $requete = $connexion->prepare(
            "SELECT idAdmin, nom, prenom, email, motdepasse, role
             FROM administrateur
             WHERE email = ?"
        );

        $requete->execute([$email]);

        $administrateur = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$administrateur) {

            $message = "Email ou mot de passe incorrect.";

        } elseif (
            !password_verify(
                $motdepasse,
                $administrateur['motdepasse']
            )
        ) {

            $message = "Email ou mot de passe incorrect.";

        } elseif (
            $administrateur['role'] !== 'Administrateur' &&
            $administrateur['role'] !== 'SuperAdmin'
        ) {

            $message = "Vous n'avez pas les droits administrateur.";

        } else {

            // Connexion réussie
            session_regenerate_id(true);

            $_SESSION['admin_connecte'] = true;

            $_SESSION['idAdmin'] = $administrateur['idAdmin'];
            $_SESSION['nom'] = $administrateur['nom'];
            $_SESSION['prenom'] = $administrateur['prenom'];
            $_SESSION['email'] = $administrateur['email'];
            $_SESSION['role'] = $administrateur['role'];

            // Redirection vers le tableau de bord
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css" rel="stylesheet">
   <!--  style css  -->
     <link rel="stylesheet" href="assets/css/connexion.css?v=2">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Espace entre le header fixe et le formulaire */
        .login {
            min-height: 100vh;
            height: auto;
            padding-block: 8rem 3rem;
        }

        .login__box--error { height: 52px; }

        .login__group:has(.login__box--error) + .login__forgot {
            margin-bottom: 1.5rem;
        }

        .login__box--error { border-color: #ff6b6b; }
        .login__box--error .login__icon { color: #ff6b6b; }
        .login__error-text {
            display: block;
            width: 100%;
            padding: 0 1rem 0 3rem;
            font-size: .85rem;
            line-height: 1.3;
            color: #ff8c8c;
            text-align: left;
        }
    </style>

     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo hemip.jpg" style="border-radius: 20px;">
    
     <title>Connexion | HEMIP</title>
</head>
<body>
    <header class="header">

    <nav class="navbar">

        <!-- LOGO -->
      <a href="#" class="logo">
    <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
    <span>HEMIP</span>
</a>


        <!-- BOUTON MOBILE -->
        <button class="menu-btn" id="menu-btn">
            <i class="ri-menu-line"></i>
        </button>


        <!-- MENU -->
        <ul class="nav-list" id="nav-list">

            <li>
                <a href="index.php">Accueil</a>
            </li>


            <!-- DROPDOWN FORMATION -->
            <li class="dropdown">

                <button class="dropdown-btn">
                    Formation
                    <i class="ri-arrow-down-s-line"></i>
                </button>

                <ul class="dropdown-menu">

                    <!-- SOUS MENU pole technique-->
                    <li class="submenu">

                        <button class="submenu-btn">
                            Pôle technique

                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        <ul class="submenu-menu">
                            <li><a href="GP.html">Génie Pétrolier</a></li>
                            <li><a href="GL.html">Génie Logiciel</a></li>
                            <li><a href="RT.html">Réseaux et Télécommunication</a></li>
                            <li><a href="MI.html">Maintenance Industrielle</a></li>
                            <li><a href="AII.html">Automatisation et Informatique Industriel</a></li>
                            
                            
                          
                        </ul>

                    </li>
                    

                    <!-- SOUS MENU -->
                    <li class="submenu">

                        <button class="submenu-btn">
                            Pôle Commercial
                            <i class="ri-arrow-right-s-line"></i>
                        </button>

                        <ul class="submenu-menu">
                            <li><a href="DAE.html">Droit des Affaires et des Entreprises</a></li>
                            <li><a href="GLT.html">Gestion Logistique et Transport</a></li>
                            <li><a href="BFA.html">Banque et Finance des Assurances</a></li>
                            <li><a href="CIT.html">Commerce International et Transit</a></li>
                           <li><a href="GFC.html">Gestion Finance et Comptable</a></li>       
                         <li><a href="MRH.html">Management des Ressources Humaines</a></li> 
                        </ul>

                    </li>

                </ul>

            </li>


            <li>
                <a href="inscription.php">Inscription</a>
            </li>


            <li>
                <a href="actualite.html">Actualité</a>
            </li>


            <li>
                <a href="#footer">Contact</a>
            </li>
 <li>
                <a href="login.php"><i class="ri-admin-line"></i></a>
            </li>
            

        </ul>

    </nav>

</header>
  <section class="login">
    <div class="login__content">
        <div>
            <h2 class="login__title"> Connectez vous ici </h2>

            <form action="login.php" method="post" class="login__form">
       <div class="login__group">

      <?php if (!empty($message)): ?>
      <div class="login__box login__box--error" role="alert">
        <i class="ri-error-warning-fill login__icon"></i>
        <span class="login__error-text"><?= htmlspecialchars($message) ?></span>
      </div>
      <?php endif; ?>

      <div class="login__box">
      <i class="ri-mail-fill login__icon"></i>
      <input type="email" name="email" autocomplete="email" class="login__input" required placeholder="" id="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      <label for="email" class="login__label">Email</label>
           </div>

      <div class="login__box">
        <i class="ri-lock-2-fill login__icon"></i>
      <input type="password" name="motdepasse" autocomplete="current-password" class="login__input" required placeholder="" id="motdepasse">
      <label for="motdepasse" class="login__label">Mot de passe</label>
           </div>
       </div> 

      <a href="" class="login__forgot"> Mot de passe oublié ?</a>
      <button type="submit" class="login__button">
        Se connecter <i class="ri-send-ins-line"></i>
      </button>

    
    </form>
 </div>

        <div class="login__image">
            <img src="assets/img/login.jpg" alt="" class="login__img">
        </div>
    </div>
  </section>  








<footer class="footer reveal">
    <div class="footer-container " id="footer">

        <!-- Présentation -->
        <div class="footer-col footer-about">
            <div class="footer-logo">
                <img src="assets/img/logo hemip.jpg" alt="Logo HEMIP">
                <span>HEMIP</span>
            </div>

            <p class="footer-slogan">
                « Nous formons des professionnels qualifiés et chevronnés »
            </p>
        </div>

        <!-- Contact -->
        <div class="footer-col">
            <h3>Contactez-nous</h3>

            <ul class="footer-contact">
                <li>
                    <i class="ri-mail-line"></i>
                    <a href="mailto:hemilaperceeinformation@gmail.com">
             hemilaperceeinformation@gmail.com
                    </a>
                </li>

                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242069149242">
                        +242 06 914 92 42
                    </a>
                </li>

                <li>
                    <i class="ri-phone-line"></i>
                    <a href="tel:+242053560677">
                        +242 06 356 06 77
                    </a>
                </li>

                <li>
                    <i class="ri-whatsapp-line"></i>
                    <a href="https://wa.me/242055865094" target="_blank">
                        WhatsApp
                    </a>
                </li>
            </ul>
        </div>

        <!-- Réseaux sociaux -->
        <div class="footer-col">
            <h3>Suivez-nous</h3>

            <p class="social-text">
                Retrouvez HEMIP sur nos réseaux sociaux.
            </p>

            <div class="social-links">

                <a href="https://vm.tiktok.com/ZS9kFSarLrbTD-96s9G/" class="social-link" aria-label="TikTok">
                    <i class="ri-tiktok-line"></i>
                </a>

                <a href="https://www.facebook.com/hemipcongo" class="social-link" aria-label="Facebook">
                    <i class="ri-facebook-fill"></i>
                </a>

                <a href="https://wa.me/242055865094"
                   class="social-link"
                   aria-label="WhatsApp"
                   target="_blank">
                    <i class="ri-whatsapp-line"></i>
                </a>

            </div>
        </div>

    </div>

    <!-- Bas du footer -->
    <div class="footer-bottom">
        <p>
            © 2026 HEMIP. Tous droits réservés.
        </p>

        <p>
            Haute École de Management et d'Ingénierie la Percée
        </p>
    </div>
</footer>

<!-- GSAP -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>    
<!-- JS -->
<script src="assets/js/connexion.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>