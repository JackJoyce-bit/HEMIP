<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }

require_once __DIR__ . '/includes/role-guard.php';
require_once __DIR__ . '/includes/csrf.php';
hemip_start_session();
require_once __DIR__ . '/connexion.php';

$adminConnecte = !empty($_SESSION['admin_connecte']);
$etudiantConnecte = !empty($_SESSION['etudiant_connecte']);
$message = '';
$typeConnexion = 'admin';
$roleAdminDemande = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeSoumis = $_POST['type_connexion'] ?? '';
    $typeConnexion = in_array($typeSoumis, ['etudiant', 'enseignant', 'admin'], true) ? $typeSoumis : 'admin';
    $emailSaisi = $_POST['email'] ?? '';
    $motdepasseSaisi = $_POST['motdepasse'] ?? '';
    $email = is_string($emailSaisi) ? trim($emailSaisi) : '';
    $motdepasse = is_string($motdepasseSaisi) ? $motdepasseSaisi : '';

    if (!hemip_csrf_valid($_POST['csrf'] ?? null)) {
        $message = 'Session expirée. Rechargez la page puis réessayez.';
    } elseif ($typeConnexion === 'etudiant') {
        $matriculeSaisi = $_POST['matricule'] ?? '';
        $matricule = is_string($matriculeSaisi) ? trim($matriculeSaisi) : '';
        $messageErreurEtudiant = 'Identifiant(s) invalide(s). Veuillez réessayer.';

        if ($email === '' || $motdepasse === '' || $matricule === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = $messageErreurEtudiant;
        } else {
            $requete = $connexion->prepare(
                "SELECT idCandidat, nom, prenom, email, motdepasse, matricule_ancien
                 FROM candidat WHERE matricule_ancien = :matricule AND email = :email LIMIT 1"
            );
            $requete->execute([':matricule' => $matricule, ':email' => $email]);
            $etudiant = $requete->fetch(PDO::FETCH_ASSOC);

            $inscrit = false;
            if ($etudiant) {
                $requeteStatut = $connexion->prepare(
                    "SELECT idPreinscription FROM preinscription
                     WHERE idCandidat = :idCandidat AND statut = 'Validée' LIMIT 1"
                );
                $requeteStatut->execute([':idCandidat' => $etudiant['idCandidat']]);
                $inscrit = (bool) $requeteStatut->fetchColumn();
            }

            if (!$etudiant || !$inscrit || !password_verify($motdepasse, $etudiant['motdepasse'])) {
                $message = $messageErreurEtudiant;
            } else {
                hemip_clear_identity();
                session_regenerate_id(true);
                unset($_SESSION['_hemip_csrf']);
                $_SESSION['etudiant_connecte'] = true;
                $_SESSION['idCandidat'] = (int) $etudiant['idCandidat'];
                $_SESSION['etudiant_nom'] = $etudiant['nom'];
                $_SESSION['etudiant_prenom'] = $etudiant['prenom'];
                $_SESSION['etudiant_email'] = $etudiant['email'];
                $_SESSION['etudiant_matricule'] = $etudiant['matricule_ancien'];
                header('Location: etudiant.php');
                exit;
            }
        }
    } elseif ($typeConnexion === 'enseignant') {
        if ($email === '' || $motdepasse === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Adresse e-mail ou mot de passe invalide.';
        } else {
            try {
                $requete = $connexion->prepare(
                    'SELECT id_enseignant, nom, prenom, email, motdepasse, actif, force_changement_mdp
                     FROM enseignant WHERE email = ? LIMIT 1'
                );
                $requete->execute([$email]);
                $enseignant = $requete->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $exception) {
                $enseignant = false;
            }

            if (!$enseignant || (int) $enseignant['actif'] !== 1 || !password_verify($motdepasse, $enseignant['motdepasse'])) {
                $message = 'Adresse e-mail ou mot de passe invalide.';
            } else {
                hemip_clear_identity();
                session_regenerate_id(true);
                unset($_SESSION['_hemip_csrf']);
                $_SESSION['enseignant_connecte'] = true;
                $_SESSION['idEnseignant'] = (int) $enseignant['id_enseignant'];
                $_SESSION['nom'] = $enseignant['nom'];
                $_SESSION['prenom'] = $enseignant['prenom'];
                $_SESSION['email'] = $enseignant['email'];
                $_SESSION['role'] = 'Enseignant';
                header('Location: ' . ((int) $enseignant['force_changement_mdp'] === 1 ? 'enseignant-motdepasse.php' : 'enseignant.php'));
                exit;
            }
        }
    } else {
        $roleAdminDemande = $_POST['role_admin'] ?? '';
        $rolesAdmin = ['Administrateur', 'SuperAdmin', 'SAC', 'DAAC'];
        if (!is_string($roleAdminDemande) || !in_array($roleAdminDemande, $rolesAdmin, true)) {
            $message = 'Veuillez sélectionner un rôle administratif valide.';
        } elseif ($email === '' || $motdepasse === '') {
            $message = 'Veuillez remplir tous les champs.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Adresse e-mail invalide.';
        } else {
            $requete = $connexion->prepare(
                'SELECT idAdmin, nom, prenom, email, motdepasse, role
                 FROM administrateur WHERE email = ? LIMIT 1'
            );
            $requete->execute([$email]);
            $administrateur = $requete->fetch(PDO::FETCH_ASSOC);

            if (!$administrateur || $administrateur['role'] !== $roleAdminDemande || !password_verify($motdepasse, $administrateur['motdepasse'])) {
                $message = 'Identifiants ou rôle incorrects.';
            } else {
                hemip_clear_identity();
                session_regenerate_id(true);
                unset($_SESSION['_hemip_csrf']);
                $_SESSION['idAdmin'] = (int) $administrateur['idAdmin'];
                $_SESSION['nom'] = $administrateur['nom'];
                $_SESSION['prenom'] = $administrateur['prenom'];
                $_SESSION['email'] = $administrateur['email'];
                $_SESSION['role'] = $administrateur['role'];

                if (in_array($roleAdminDemande, ['Administrateur', 'SuperAdmin'], true)) {
                    $_SESSION['admin_connecte'] = true;
                    header('Location: dashboard.php');
                } elseif ($roleAdminDemande === 'SAC') {
                    $_SESSION['sac_connecte'] = true;
                    header('Location: sac.php');
                } else {
                    $_SESSION['daac_connecte'] = true;
                    header('Location: daac.php');
                }
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>

    <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
     <!-- Remix Icon -->
    <link href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>" rel="stylesheet">
   <!--  style css  -->
    <link rel="stylesheet" href="<?= asset('assets/css/connexion.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <style>
        /* Espace entre le header fixe et le formulaire */
        .login {
            min-height: 100vh;
            height: auto;
            padding-block: 8rem 3rem;
        }

        .login__box--error { height: auto; min-height: 52px; padding-block: 8px; }

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

        /* ===== Onglets Étudiant / Administrateur ===== */
        .login__tabs {
            display: flex;
            gap: .5rem;
            margin-bottom: 1.75rem;
            padding: .3rem;
            border-radius: .75rem;
            background: rgba(255, 255, 255, .12);
        }

        .login__tab {
            flex: 1;
            padding: .7rem .5rem;
            border: 0;
            border-radius: .55rem;
            background: transparent;
            color: inherit;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: background .25s, color .25s;
        }

        .login__tab.active {
            background: #1094d7;
            color: #fff;
        }

        .login__form[hidden] { display: none; }
    </style>

     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">

     <title>Connexion | HEMIP</title>
    <meta name="theme-color" content="#1094d7">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <script>window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();window.__hemipInstallPrompt=e;});</script>
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    <link rel="stylesheet" href="<?= asset('assets/css/pwa.css') ?>">
  <link rel="stylesheet" href="<?= asset('assets/css/hemip-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">
</head>
<body>
    <header class="header">

    <nav class="navbar">

        <!-- LOGO -->
      <a href="index.php" class="logo">
    <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
    <span>HEMIP</span>
</a>


        <!-- BOUTON MOBILE -->
        <button class="menu-btn" id="menu-btn" type="button" aria-label="Ouvrir le menu de navigation" aria-expanded="false" aria-controls="nav-list">
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
                            <li><a href="EE.html">Électrotechnique et Électronique</a></li>
                            <li><a href="GC.html">Génie Chimique</a></li>
                            <li><a href="RP.html">Raffinage et Pétrochimie</a></li>
                            <li><a href="ENR.html">Énergies Renouvelables</a></li>



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
                <a href="actualite.php">Actualités</a>
            </li>

            <li>
                <a href="realisations.php">
                    Nos réalisations
                </a>
            </li>

            <li>
                <a href="contact.php">Contact</a>
            </li>

            <li><a href="faq.php">FAQ</a></li>

            <li><a href="a-propos.php">À propos</a></li>
            <?php if ($adminConnecte): ?>

            <!-- Accès au tableau de bord -->
            <li>
                <a href="dashboard.php" title="Tableau de bord">
                    <i class="ri-dashboard-line"></i>
                </a>
            </li>

            <!-- Administrateur connecté -->
            <li class="admin-status">
                <i class="ri-admin-line" style="color: var(--hemip-bg);"></i>
                <span style="color: var(--hemip-bg); font-size: 15px;">Connecté</span>
            </li>

           <?php elseif ($etudiantConnecte): ?>

            <!-- Accès à l'espace étudiant -->
            <li>
                <a href="etudiant.php" title="Mon espace étudiant">
                    <i class="ri-user-3-line"></i>
                </a>
            </li>

            <!-- Étudiant connecté -->
            <li class="admin-status">
                <i class="ri-user-line" style="color: var(--hemip-bg);"></i>
                <span style="color: var(--hemip-bg); font-size: 15px;">Connecté</span>
            </li>

           <?php else: ?>

            <!-- Connexion -->
            <li>
                <a href="login.php" title="Connexion">
                    <i class="ri-admin-line"></i>
                </a>
            </li>

           <?php endif; ?>


        </ul>

    </nav>

</header>
  <section class="login">
    <div class="login__content">
        <div>
            <h2 class="login__title"> Connectez vous ici </h2>

            <!-- Onglets : même principe que « Se réinscrire / S'inscrire » -->
            <div class="login__tabs" role="tablist">
                <button type="button" role="tab" id="tab-etudiant"
                        class="login__tab<?= $typeConnexion === 'etudiant' ? ' active' : '' ?>"
                        aria-selected="<?= $typeConnexion === 'etudiant' ? 'true' : 'false' ?>"
                        aria-controls="form-etudiant">
                    Étudiant
                </button>
                <button type="button" role="tab" id="tab-enseignant"
                        class="login__tab<?= $typeConnexion === 'enseignant' ? ' active' : '' ?>"
                        aria-selected="<?= $typeConnexion === 'enseignant' ? 'true' : 'false' ?>"
                        aria-controls="form-enseignant">
                    Enseignant
                </button>
                <button type="button" role="tab" id="tab-admin"
                        class="login__tab<?= $typeConnexion === 'admin' ? ' active' : '' ?>"
                        aria-selected="<?= $typeConnexion === 'admin' ? 'true' : 'false' ?>"
                        aria-controls="form-admin">
                    Administrateur
                </button>
            </div>

            <!-- ================= FORMULAIRE ÉTUDIANT ================= -->
            <form action="login.php" method="post" class="login__form" id="form-etudiant"
                  role="tabpanel" aria-labelledby="tab-etudiant"
                  <?= $typeConnexion === 'etudiant' ? '' : 'hidden' ?>>

                <input type="hidden" name="type_connexion" value="etudiant">
                <?= hemip_csrf_input() ?>

                <div class="login__group">

                  <?php if (!empty($message) && $typeConnexion === 'etudiant'): ?>
                  <div class="login__box login__box--error" role="alert">
                    <i class="ri-error-warning-fill login__icon"></i>
                    <span class="login__error-text"><?= htmlspecialchars($message) ?></span>
                  </div>
                  <?php endif; ?>

                  <div class="login__box">
                    <i class="ri-mail-fill login__icon"></i>
                    <input type="email" name="email" autocomplete="email" class="login__input" required placeholder="" id="email-etudiant" value="<?= $typeConnexion === 'etudiant' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                    <label for="email-etudiant" class="login__label">Email</label>
                  </div>

                  <div class="login__box">
                    <i class="ri-lock-2-fill login__icon"></i>
                    <input type="password" name="motdepasse" autocomplete="current-password" class="login__input" required placeholder="" id="motdepasse-etudiant">
                    <label for="motdepasse-etudiant" class="login__label">Mot de passe</label>
                  </div>

                  <div class="login__box">
                    <i class="ri-id-card-fill login__icon"></i>
                    <input type="text" name="matricule" autocomplete="off" class="login__input" required placeholder="" id="matricule-etudiant" value="<?= $typeConnexion === 'etudiant' ? htmlspecialchars($_POST['matricule'] ?? '') : '' ?>">
                    <label for="matricule-etudiant" class="login__label">Matricule</label>
                  </div>

                </div>

                <a href="mailto:hemilaperceeinformation@gmail.com?subject=HEMIP%20-%20R%C3%A9initialisation%20du%20mot%20de%20passe%20%C3%A9tudiant" class="login__forgot"> Mot de passe oublié ?</a>
                <button type="submit" class="login__button">
                  Se connecter <i class="ri-send-ins-line"></i>
                </button>

            </form>

            <!-- ================= FORMULAIRE ENSEIGNANT ================= -->
            <form action="login.php" method="post" class="login__form" id="form-enseignant"
                  role="tabpanel" aria-labelledby="tab-enseignant"
                  <?= $typeConnexion === 'enseignant' ? '' : 'hidden' ?>>

                <input type="hidden" name="type_connexion" value="enseignant">
                <?= hemip_csrf_input() ?>

                <div class="login__group">
                  <?php if (!empty($message) && $typeConnexion === 'enseignant'): ?>
                  <div class="login__box login__box--error" role="alert">
                    <i class="ri-error-warning-fill login__icon"></i>
                    <span class="login__error-text"><?= htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                  </div>
                  <?php endif; ?>

                  <div class="login__box">
                    <i class="ri-mail-fill login__icon"></i>
                    <input type="email" name="email" autocomplete="email" class="login__input" required placeholder="" id="email-enseignant" value="<?= $typeConnexion === 'enseignant' ? htmlspecialchars(is_string($_POST['email'] ?? null) ? $_POST['email'] : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : '' ?>">
                    <label for="email-enseignant" class="login__label">Email professionnel</label>
                  </div>

                  <div class="login__box">
                    <i class="ri-lock-2-fill login__icon"></i>
                    <input type="password" name="motdepasse" autocomplete="current-password" class="login__input" required placeholder="" id="motdepasse-enseignant">
                    <label for="motdepasse-enseignant" class="login__label">Mot de passe</label>
                  </div>
                </div>

                <button type="submit" class="login__button">
                  Se connecter <i class="ri-send-ins-line"></i>
                </button>
            </form>

            <!-- ================= FORMULAIRE ADMINISTRATEUR ================= -->
            <form action="login.php" method="post" class="login__form" id="form-admin"
                  role="tabpanel" aria-labelledby="tab-admin"
                  <?= $typeConnexion === 'admin' ? '' : 'hidden' ?>>

                <input type="hidden" name="type_connexion" value="admin">
                <?= hemip_csrf_input() ?>

                <div class="login__group">

                  <div class="login__box">
                    <i class="ri-admin-line login__icon"></i>
                    <select name="role_admin" id="role-admin" class="login__input" required>
                      <option value="" disabled <?= $roleAdminDemande === '' ? 'selected' : '' ?>>Sélectionnez votre rôle</option>
                      <?php foreach (['Administrateur', 'SuperAdmin', 'SAC', 'DAAC'] as $roleOption): ?>
                        <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" <?= $roleAdminDemande === $roleOption ? 'selected' : '' ?>><?= htmlspecialchars($roleOption, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></option>
                      <?php endforeach; ?>
                    </select>
                    <label for="role-admin" class="login__label">Rôle administratif</label>
                  </div>

                  <?php if (!empty($message) && $typeConnexion === 'admin'): ?>
                  <div class="login__box login__box--error" role="alert">
                    <i class="ri-error-warning-fill login__icon"></i>
                    <span class="login__error-text"><?= htmlspecialchars($message) ?></span>
                  </div>
                  <?php endif; ?>

                  <div class="login__box">
                    <i class="ri-mail-fill login__icon"></i>
                    <input type="email" name="email" autocomplete="email" class="login__input" required placeholder="" id="email" value="<?= $typeConnexion === 'admin' ? htmlspecialchars($_POST['email'] ?? '') : '' ?>">
                    <label for="email" class="login__label">Email</label>
                  </div>

                  <div class="login__box">
                    <i class="ri-lock-2-fill login__icon"></i>
                    <input type="password" name="motdepasse" autocomplete="current-password" class="login__input" required placeholder="" id="motdepasse">
                    <label for="motdepasse" class="login__label">Mot de passe</label>
                  </div>

                </div>

                <a href="mailto:hemilaperceeinformation@gmail.com?subject=HEMIP%20-%20R%C3%A9initialisation%20du%20mot%20de%20passe%20administrateur" class="login__forgot"> Mot de passe oublié ?</a>
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
                <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
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
                    <a href="tel:+242063560677">
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

<!-- GSAP (hébergé localement) -->
<script src="<?= asset('assets/vendor/gsap/gsap.min.js') ?>"></script>    
<!-- JS -->
<script src="<?= asset('assets/js/connexion.js') ?>"></script>
<script src="<?= asset('assets/js/main.js') ?>"></script>
  <script src="<?= asset('assets/js/pwa.js') ?>" defer></script>
<script>
/* Bascule entre les trois types de connexion */
(function () {
  var tabs = {
    etudiant: { btn: document.getElementById("tab-etudiant"), form: document.getElementById("form-etudiant") },
    enseignant: { btn: document.getElementById("tab-enseignant"), form: document.getElementById("form-enseignant") },
    admin: { btn: document.getElementById("tab-admin"), form: document.getElementById("form-admin") }
  };

  function afficher(nom) {
    Object.keys(tabs).forEach(function (cle) {
      var actif = cle === nom;
      tabs[cle].btn.classList.toggle("active", actif);
      tabs[cle].btn.setAttribute("aria-selected", actif ? "true" : "false");
      tabs[cle].form.hidden = !actif;
    });
  }

  Object.keys(tabs).forEach(function (cle) {
    tabs[cle].btn.addEventListener("click", function () { afficher(cle); });
  });
})();

/* Filet de sécurité : si une animation (GSAP) ne démarre pas, le bloc de connexion reste visible */
window.addEventListener("load",function(){setTimeout(function(){
  document.querySelectorAll(".login__content,.login__image,.login__form,.login__title").forEach(function(el){
    if (el.hidden) return;
    var cs=getComputedStyle(el);
    if(parseFloat(cs.opacity)===0||cs.visibility==="hidden"){
      el.style.setProperty("opacity","1","important");el.style.setProperty("visibility","visible","important");el.style.setProperty("transform","none","important");
    }
  });
},1200);});
</script>
</body>
</html>
