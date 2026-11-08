<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }
session_start();

$adminConnecte = isset($_SESSION['admin_connecte'])
    && $_SESSION['admin_connecte'] === true;
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

    <!-- Remix Icon -->
    <link
        href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/pwa.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/home-sections.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/hemip-theme.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/faq.css') ?>">

     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">
    <title>Questions fréquentes | HEMIP</title>
    <meta name="description" content="Réponses aux questions fréquentes sur les formations, l'inscription, les frais et la vie étudiante à HEMIP.">

    <meta name="theme-color" content="#23a8eb">
    <meta name="application-name" content="HEMIP">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="HEMIP">
    <script>window.addEventListener("beforeinstallprompt",function(e){e.preventDefault();window.__hemipInstallPrompt=e;});</script>
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/icons/hemip-192.png">
    
    <link rel="stylesheet" href="<?= asset('assets/css/responsive.css') ?>">

    <style>
        /* Augmentation de la taille du texte pour les FAQ */
        .faq-answer p, .faq-answer li {
            font-size: 1.1rem;
            line-height: 1.8;
        }
    </style>
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
            <span aria-hidden="true">☰</span>
        </button>


        <!-- MENU -->
        <ul class="nav-list" id="nav-list">

            <li>
                <a href="index.php">Accueil</a>
            </li>


            <!-- DROPDOWN FORMATION -->
            <li class="dropdown" id="formations">

                <button class="dropdown-btn" aria-expanded="false">
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
                <a href="realisations.php">Nos réalisations</a>
            </li>

            <li>
                <a href="contact.php#contact">Contact</a>
            </li>
            <li><a href="faq.php" aria-current="page">FAQ</a></li>

            <li>
                <a href="a-propos.php">À propos</a>
            </li>

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

           <?php else: ?>

            <!-- Connexion administrateur -->
            <li>
                <a href="login.php" title="Connexion administrateur">
                    <i class="ri-admin-line"></i>
                </a>
            </li>

           <?php endif; ?>
            

        </ul>

    </nav>

</header>

    <main>
        <section class="faq-hero" aria-labelledby="faq-title">
            <span class="faq-eyebrow">Questions fréquentes</span>
            <h1 id="faq-title">Une question ?<br><em>Commençons ici.</em></h1>
            <p>Les informations essentielles pour avancer sereinement dans votre projet d’études à HEMIP.</p>
        </section>

        <div class="faq-wrap">
            <div class="faq-layout">

                <div class="faq-list" id="faq-list">

                    <details class="faq-item">
                        <summary>Quelles filières propose HEMIP ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>HEMIP propose deux pôles de formation :</p>
                            <ul>
                                <li><strong>Pôle Technique</strong> : Génie Pétrolier, Génie Logiciel, Réseaux et Télécommunication, Maintenance Industrielle, Automatisation et Informatique Industriel, Génie Chimique, Raffinage et Pétrochimie, Énergies Renouvelables.
                                <li><strong>Pôle Commercial</strong> : Droit des Affaires et des Entreprises, Gestion Logistique et Transport, Banque et Finance des Assurances, Commerce International et Transit, Gestion Finance et Comptable, Management des Ressources Humaines.</li>
                            </ul>
                            <p>Chaque filière est présentée en détail dans la rubrique <a href="index.php#formations">Formation</a> du site.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Comment se déroule l’inscription à HEMIP ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>L’inscription se fait en ligne depuis la page <a href="inscription.php">Inscription</a>, en deux parcours selon votre situation :</p>
                            <ul>
                                <li><strong>Nouvelle inscription</strong> : vous renseignez vos informations et déposez les pièces demandées (acte de naissance, diplôme ou attestation de réussite, photo d’identité, relevés de notes, certificat médical et attestation d’assurance).</li>
                                <li><strong>Réinscription</strong> : vous indiquez votre matricule d’étudiant et votre adresse e-mail pour retrouver votre dossier.</li>
                            </ul>
                            <p>Un code de candidature vous est remis à la fin. Conservez-le : il permet de suivre votre demande.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Quelle est la durée d’une formation ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Les parcours suivent le système LMD et s’organisent généralement sur trois niveaux de Licence professionnelle, puis deux niveaux de Master. La durée exacte et l’organisation des semestres dépendent de la filière choisie et sont confirmées au moment de l’admission par l’administration.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>L’inscription en ligne suffit-elle pour être admis ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Non. Une préinscription en ligne ne vaut pas une admission définitive. Elle ouvre l’instruction de votre dossier par l’administration. Votre admission est confirmée lorsque votre demande est validée et que votre inscription est acceptée ; un numéro d’étudiant vous est alors attribué et transmis par e-mail.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Quels documents dois-je fournir ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Pour une nouvelle inscription, vous déposez les pièces suivantes au format PDF, JPG ou PNG :</p>
                            <ul>
                                <li>Acte ou extrait d’acte de naissance ;</li>
                                <li>Diplôme ou attestation de réussite au Baccalauréat légalisée ;</li>
                                <li>Photo d’identité sur fond blanc ;</li>
                                <li>Copie légalisée des relevés de notes (pour une inscription en 2ᵉ ou 3ᵉ année) ;</li>
                                <li>Certificat médical ;</li>
                                <li>Attestation d’assurance.</li>
                            </ul>
                            <p>Assurez-vous que chaque fichier est lisible avant l’envoi.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Puis-je me réinscrire si j’ai déjà étudié à HEMIP ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Oui. La réinscription est réservée aux anciens étudiants dont la précédente inscription a été validée. Il vous faut votre numéro d’étudiant (matricule) et l’adresse e-mail associée à votre dossier, afin de retrouver votre parcours et choisir une nouvelle filière ou un nouveau niveau.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Quand ont lieu les cours et comment se passe la journée type ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Les enseignements alternent cours théoriques et travaux pratiques, menés dans les salles, ateliers et laboratoires de l’école (mécanique, électricité, chimie, informatique, électronique). Une bibliothèque physique et numérique est disponible sur place.</p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>HEMIP est-elle reconnue par l’État ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Oui. HEMIP est une institution tertiaire privée agréée de l’Enseignement Supérieur et Universitaire de la République du Congo, présente depuis 2013 à Pointe-Noire, avec une extension à NKayi. Agréée définitivement par l'Etat sous le numéro : <strong>0454 MES-CAB.DGESUP.DAAC</strong></p>
                        </div>
                    </details>

                    <details class="faq-item">
                        <summary>Où se trouve HEMIP et comment nous contacter ?<i class="ri-arrow-down-s-line faq-chevron" aria-hidden="true"></i></summary>
                        <div class="faq-answer">
                            <p>Le campus se situe à Pointe-Noire, au centre-ville, derrière la tour MAYOMBE.</p>
                            <p>Vous pouvez nous écrire à <a href="mailto:hemilaperceeinformation@gmail.com">hemilaperceeinformation@gmail.com</a>, nous appeler au <a href="tel:+242069149242">+242 06 914 92 42</a> ou au <a href="tel:+242063560677">+242 06 356 06 77</a>, ou utiliser le <a href="contact.php#contact">formulaire de contact</a>.</p>
                        </div>
                    </details>

                </div>

                <aside class="faq-aside">
                    <div class="faq-aside-icon"><i class="ri-question-line" aria-hidden="true"></i></div>
                    <h2>Vous ne trouvez pas votre réponse ?</h2>
                    <p>Notre équipe reste disponible pour répondre à vos questions sur les filières, les inscriptions et la vie étudiante.</p>
                    <div class="faq-aside-links">
                        <a href="contact.php#contact"><i class="ri-mail-send-line" aria-hidden="true"></i><span>Écrire à l’école</span></a>
                        <a href="inscription.php"><i class="ri-file-add-line" aria-hidden="true"></i><span>S’inscrire en ligne</span></a>
                        <a href="tel:+242069149242"><i class="ri-phone-line" aria-hidden="true"></i><span>+242 06 914 92 42</span></a>
                    </div>
                </aside>

            </div>
        </div>
    </main>

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
            Haute École de Management et de l'Ingénierie la Percée
        </p>
    </div>
</footer>

<script src="<?= asset('assets/js/main.js') ?>"></script>
  <script src="<?= asset('assets/js/pwa.js') ?>" defer></script>
</body>
</html>
