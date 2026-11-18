<?php
if (!function_exists('asset')) { function asset($p) { $f = __DIR__ . '/' . $p; return $p . '?v=' . (is_file($f) ? filemtime($f) : '1'); } }

session_start();

require_once "connexion.php";

/* =========================================================
   DÉCONNEXION (étudiant uniquement)
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deconnexion') {

    foreach (array_keys($_SESSION) as $cle) {
        if ($cle === 'etudiant_connecte' || $cle === 'idCandidat' || strpos($cle, 'etudiant_') === 0) {
            unset($_SESSION[$cle]);
        }
    }

    session_regenerate_id(true);
    header("Location: login.php");
    exit();
}

/* =========================================================
   ACCÈS RÉSERVÉ AUX ÉTUDIANTS CONNECTÉS
   ========================================================= */

if (
    empty($_SESSION['etudiant_connecte']) ||
    $_SESSION['etudiant_connecte'] !== true ||
    empty($_SESSION['idCandidat'])
) {
    header("Location: login.php");
    exit();
}

$idCandidat = (int) $_SESSION['idCandidat'];

/* =========================================================
   DONNÉES DE L'ÉTUDIANT
   ========================================================= */

$requete = $connexion->prepare(
    "SELECT idCandidat, nom, prenom, email, telephone, date_naissance,
            sexe, adresse, matricule_ancien
     FROM candidat
     WHERE idCandidat = :id
     LIMIT 1"
);
$requete->execute([':id' => $idCandidat]);
$etudiant = $requete->fetch(PDO::FETCH_ASSOC);

if (!$etudiant) {
    header("Location: login.php");
    exit();
}

// Dernière inscription validée : filière, niveau, année académique
$requete = $connexion->prepare(
    "SELECT p.idPreinscription, p.idFiliere, p.niveau, p.annee_academique,
            f.nomFiliere
     FROM preinscription p
     LEFT JOIN filiere f ON f.idFiliere = p.idFiliere
     WHERE p.idCandidat = :id
       AND p.statut = 'Validée'
     ORDER BY p.idPreinscription DESC
     LIMIT 1"
);
$requete->execute([':id' => $idCandidat]);
$inscription = $requete->fetch(PDO::FETCH_ASSOC);

if (!$inscription) {
    // Plus d'inscription validée : l'accès n'est plus autorisé
    header("Location: login.php");
    exit();
}

// Documents déposés à l'inscription
$requete = $connexion->prepare(
    "SELECT d.nom_document, d.type_document, d.chemin_fichier
     FROM document d
     INNER JOIN preinscription p ON p.idPreinscription = d.idPreinscription
     WHERE p.idCandidat = :id"
);
$requete->execute([':id' => $idCandidat]);
$documents = $requete->fetchAll(PDO::FETCH_ASSOC);

// Notifications
$requete = $connexion->prepare(
    "SELECT n.type_notification, n.message
     FROM notification n
     INNER JOIN preinscription p ON p.idPreinscription = n.id_preinscription
     WHERE p.idCandidat = :id"
);
$requete->execute([':id' => $idCandidat]);
$notifications = $requete->fetchAll(PDO::FETCH_ASSOC);

/* =========================================================
   OUTILS D'AFFICHAGE
   ========================================================= */

function e($valeur): string
{
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
}

$libellesNiveau = [
    '1ere-annee' => '1ère année',
    '2eme-annee' => '2ème année',
    '3eme-annee' => '3ème année',
    'master-1'   => 'Master 1',
    'master-2'   => 'Master 2',
];

$niveau = $inscription['niveau'];
$niveauLibelle = $libellesNiveau[$niveau] ?? $niveau;

$dateNaissance = '|';
if (!empty($etudiant['date_naissance'])) {
    $d = date_create($etudiant['date_naissance']);
    if ($d) {
        $dateNaissance = $d->format('d/m/Y');
    }
}

$initiales = mb_strtoupper(
    mb_substr($etudiant['prenom'], 0, 1) . mb_substr($etudiant['nom'], 0, 1)
);

// Photo d'identité (si elle a été déposée au format image)
$photo = null;
foreach ($documents as $doc) {
    if ($doc['type_document'] === 'Photo') {
        $ext = strtolower(pathinfo($doc['chemin_fichier'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png'], true) && is_file(__DIR__ . '/' . $doc['chemin_fichier'])) {
            $photo = $doc['chemin_fichier'];
        }
        break;
    }
}

/* ---------------------------------------------------------
   EMPLOI DU TEMPS (image)
   Déposez l'image dans assets/img/emplois-du-temps/ :
     emploi-{idFiliere}-{niveau}.jpg   (ex. emploi-3-2eme-annee.jpg)
   ou, pour toute la filière :
     emploi-{idFiliere}.jpg
   Formats acceptés : jpg, jpeg, png, webp
   --------------------------------------------------------- */

$emploiDuTemps = null;
$dossierEmploi = 'assets/img/emplois-du-temps/';
$idFiliere = (int) $inscription['idFiliere'];
$niveauSur = preg_replace('/[^A-Za-z0-9-]/', '', (string) $niveau);

foreach (["emploi-$idFiliere-$niveauSur", "emploi-$idFiliere"] as $base) {
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file(__DIR__ . '/' . $dossierEmploi . $base . '.' . $ext)) {
            $emploiDuTemps = $dossierEmploi . $base . '.' . $ext;
            break 2;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Espace étudiant | HEMIP</title>

    <link href="<?= asset('assets/vendor/remixicon/remixicon.css') ?>" rel="stylesheet">
    <link rel="icon" type="image/png" href="assets/icons/hemip-192.png">
    <meta name="theme-color" content="#1094d7">
    <link rel="manifest" href="manifest.json" crossorigin="use-credentials">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/hemip-180.png">

    <style>
        :root {
            --bleu: #1094d7;
            --bleu-fonce: #0b6fa3;
            --fond: #f1f5f9;
            --carte: #ffffff;
            --texte: #1e293b;
            --texte-doux: #64748b;
            --bordure: #e2e8f0;
            --haut: 64px;
            --lateral: 310px;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --fond: #0f172a;
                --carte: #1e293b;
                --texte: #e2e8f0;
                --texte-doux: #94a3b8;
                --bordure: #334155;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
            background: var(--fond);
            color: var(--texte);
            min-height: 100vh;
            padding-top: calc(var(--haut) + env(safe-area-inset-top, 0px));
        }

        /* ================= BANNIÈRE HORIZONTALE ================= */
        .banniere {
            position: fixed;
            inset: 0 0 auto 0;
            z-index: 50;
            height: calc(var(--haut) + env(safe-area-inset-top, 0px));
            padding: env(safe-area-inset-top, 0px) 1rem 0;
            display: flex;
            align-items: center;
            gap: .75rem;
            background: var(--bleu);
            color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .18);
        }

        .banniere__bouton {
            flex: none;
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: .6rem;
            background: rgba(255, 255, 255, .18);
            color: #fff;
            font-size: 1.4rem;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: background .2s;
        }
        .banniere__bouton:hover { background: rgba(255, 255, 255, .3); }

        .banniere__logo {
            flex: none;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: #fff;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.15rem;
        }
        .banniere__logo img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }

        .fonctionnalites {
            flex: 1;
            min-width: 0;
            display: flex;
            gap: .35rem;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .fonctionnalites::-webkit-scrollbar { display: none; }

        .fonction {
            flex: none;
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem .95rem;
            border: 0;
            border-radius: 2rem;
            background: transparent;
            color: #fff;
            font: inherit;
            font-size: .93rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: background .2s;
        }
        .fonction:hover { background: rgba(255, 255, 255, .2); }
        .fonction.active { background: #fff; color: var(--bleu-fonce); }

        .banniere__deconnexion { flex: none; }
        .banniere__deconnexion button {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem .95rem;
            border: 1px solid rgba(255, 255, 255, .6);
            border-radius: 2rem;
            background: transparent;
            color: #fff;
            font: inherit;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .2s;
        }
        .banniere__deconnexion button:hover { background: rgba(255, 255, 255, .22); }

        /* ================= BANDE VERTICALE (infos personnelles) ================= */
        .lateral {
            position: fixed;
            top: calc(var(--haut) + env(safe-area-inset-top, 0px));
            left: 0;
            bottom: 0;
            z-index: 40;
            width: var(--lateral);
            overflow-y: auto;
            padding: 1.5rem 1.25rem 2rem;
            background: var(--carte);
            border-right: 1px solid var(--bordure);
            box-shadow: 4px 0 18px rgba(0, 0, 0, .08);
            /* Effet déroulant : la bande glisse vers la gauche pour se fermer */
            transform: translateX(-100%);
            transition: transform .4s cubic-bezier(.65, 0, .35, 1);
        }
        body.lateral-ouvert .lateral { transform: translateX(0); }

        .profil { text-align: center; margin-bottom: 1.5rem; }
        .profil__photo {
            width: 110px;
            height: 110px;
            margin: 0 auto .9rem;
            border-radius: 50%;
            border: 4px solid var(--bleu);
            object-fit: cover;
            display: grid;
            place-items: center;
            background: var(--bleu);
            color: #fff;
            font-size: 2.2rem;
            font-weight: 700;
        }
        .profil__nom { font-size: 1.15rem; font-weight: 700; }
        .profil__matricule {
            display: inline-block;
            margin-top: .45rem;
            padding: .2rem .75rem;
            border-radius: 2rem;
            background: rgba(16, 148, 215, .14);
            color: var(--bleu);
            font-size: .82rem;
            font-weight: 700;
            letter-spacing: .03em;
        }

        .infos { list-style: none; }
        .infos li {
            display: flex;
            gap: .75rem;
            padding: .8rem 0;
            border-top: 1px solid var(--bordure);
        }
        .infos i { color: var(--bleu); font-size: 1.15rem; margin-top: .1rem; }
        .infos small {
            display: block;
            color: var(--texte-doux);
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .infos span { word-break: break-word; font-size: .95rem; }

        /* ================= CORPS DE PAGE ================= */
        .contenu {
            padding: 1.75rem 1.25rem 3rem;
            transition: margin-left .4s cubic-bezier(.65, 0, .35, 1);
        }
        .contenu__interieur { max-width: 1100px; margin: 0 auto; }

        .titre-page {
            display: flex;
            align-items: center;
            gap: .6rem;
            margin-bottom: .35rem;
            font-size: 1.6rem;
        }
        .titre-page i { color: var(--bleu); }
        .sous-titre { color: var(--texte-doux); margin-bottom: 1.5rem; }

        .carte {
            background: var(--carte);
            border: 1px solid var(--bordure);
            border-radius: 1rem;
            padding: 1.25rem;
        }

        .panneau { display: none; }
        .panneau.active { display: block; animation: apparition .35s ease; }
        @keyframes apparition { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        /* Emploi du temps */
        .emploi img {
            display: block;
            width: 100%;
            height: auto;
            border-radius: .6rem;
        }
        .emploi__actions { margin-top: 1rem; display: flex; gap: .6rem; flex-wrap: wrap; }
        .lien-bouton {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .55rem 1rem;
            border-radius: .6rem;
            background: var(--bleu);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: .9rem;
        }
        .lien-bouton:hover { background: var(--bleu-fonce); }

        .vide {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--texte-doux);
        }
        .vide i { display: block; font-size: 2.6rem; margin-bottom: .6rem; color: var(--bleu); }

        .liste { list-style: none; }
        .liste li {
            display: flex;
            gap: .8rem;
            align-items: flex-start;
            padding: .85rem 0;
            border-top: 1px solid var(--bordure);
        }
        .liste li:first-child { border-top: 0; }
        .liste i { color: var(--bleu); font-size: 1.3rem; }
        .liste a { color: var(--bleu); font-weight: 600; text-decoration: none; }
        .liste small { display: block; color: var(--texte-doux); }

        /* ================= ÉCRANS LARGES : le corps se décale ================= */
        @media (min-width: 900px) {
            body.lateral-ouvert .contenu { margin-left: var(--lateral); }
        }

        /* ================= MOBILE ================= */
        @media (max-width: 899px) {
            .lateral { width: min(86vw, var(--lateral)); }
            .banniere__logo span { display: none; }
            .banniere__deconnexion span { display: none; }
        }

        .voile {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 30;
            background: rgba(0, 0, 0, .4);
        }
        @media (max-width: 899px) {
            body.lateral-ouvert .voile { display: block; }
        }

        @media (prefers-reduced-motion: reduce) {
            .lateral, .contenu { transition: none; }
            .panneau.active { animation: none; }
        }
    </style>
</head>
<body>

<!-- ================= BANNIÈRE : fonctionnalités ================= -->
<header class="banniere">

    <button type="button" class="banniere__bouton" id="bouton-lateral"
            aria-label="Afficher ou masquer mes informations" aria-expanded="false" aria-controls="lateral">
        <i class="ri-user-3-line"></i>
    </button>

    <a href="index.php" class="banniere__logo" title="Retour au site">
        <img src="assets/img/logo-hemip.jpg" alt="Logo HEMIP">
        <span>HEMIP</span>
    </a>

    <nav class="fonctionnalites" aria-label="Fonctionnalités">
        <button type="button" class="fonction active" data-panneau="emploi">
            <i class="ri-calendar-schedule-line"></i> Emploi du temps
        </button>
        <button type="button" class="fonction" data-panneau="cours">
            <i class="ri-book-open-line"></i> Cours
        </button>
        <button type="button" class="fonction" data-panneau="notes">
            <i class="ri-file-list-3-line"></i> Notes
        </button>
        <button type="button" class="fonction" data-panneau="documents">
            <i class="ri-folder-line"></i> Documents
        </button>
        <button type="button" class="fonction" data-panneau="notifications">
            <i class="ri-notification-3-line"></i> Notifications
        </button>
    </nav>

    <form method="post" action="etudiant.php" class="banniere__deconnexion">
        <input type="hidden" name="action" value="deconnexion">
        <button type="submit" title="Se déconnecter">
            <i class="ri-logout-box-r-line"></i> <span>Déconnexion</span>
        </button>
    </form>

</header>

<div class="voile" id="voile"></div>

<!-- ================= BANDE VERTICALE : informations personnelles ================= -->
<aside class="lateral" id="lateral" aria-label="Informations personnelles">

    <div class="profil">
        <?php if ($photo): ?>
            <img class="profil__photo" src="<?= e($photo) ?>" alt="Photo de <?= e($etudiant['prenom']) ?>">
        <?php else: ?>
            <div class="profil__photo" aria-hidden="true"><?= e($initiales) ?></div>
        <?php endif; ?>

        <div class="profil__nom"><?= e($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></div>
        <span class="profil__matricule"><?= e($etudiant['matricule_ancien']) ?></span>
    </div>

    <ul class="infos">
        <li><i class="ri-graduation-cap-line"></i>
            <div><small>Filière</small><span><?= e($inscription['nomFiliere'] ?? '|') ?></span></div></li>
        <li><i class="ri-stack-line"></i>
            <div><small>Niveau</small><span><?= e($niveauLibelle) ?></span></div></li>
        <li><i class="ri-calendar-event-line"></i>
            <div><small>Année académique</small><span><?= e($inscription['annee_academique']) ?></span></div></li>
        <li><i class="ri-mail-line"></i>
            <div><small>Email</small><span><?= e($etudiant['email']) ?></span></div></li>
        <li><i class="ri-phone-line"></i>
            <div><small>Téléphone</small><span><?= e($etudiant['telephone']) ?></span></div></li>
        <li><i class="ri-cake-2-line"></i>
            <div><small>Date de naissance</small><span><?= e($dateNaissance) ?></span></div></li>
        <li><i class="ri-user-line"></i>
            <div><small>Sexe</small><span><?= e($etudiant['sexe']) ?></span></div></li>
        <li><i class="ri-map-pin-line"></i>
            <div><small>Adresse</small><span><?= e($etudiant['adresse']) ?></span></div></li>
    </ul>

</aside>

<!-- ================= CORPS DE LA PAGE ================= -->
<main class="contenu" id="contenu">
<div class="contenu__interieur">

    <!-- EMPLOI DU TEMPS -->
    <section class="panneau active" id="panneau-emploi">
        <h1 class="titre-page"><i class="ri-calendar-schedule-line"></i> Emploi du temps</h1>
        <p class="sous-titre">
            <?= e($inscription['nomFiliere'] ?? '') ?> | <?= e($niveauLibelle) ?> | <?= e($inscription['annee_academique']) ?>
        </p>

        <div class="carte emploi">
            <?php if ($emploiDuTemps): ?>
                <img src="<?= e(asset($emploiDuTemps)) ?>" alt="Emploi du temps | <?= e($inscription['nomFiliere'] ?? '') ?>">
                <div class="emploi__actions">
                    <a class="lien-bouton" href="<?= e($emploiDuTemps) ?>" target="_blank" rel="noopener">
                        <i class="ri-zoom-in-line"></i> Agrandir
                    </a>
                    <a class="lien-bouton" href="<?= e($emploiDuTemps) ?>" download>
                        <i class="ri-download-2-line"></i> Télécharger
                    </a>
                </div>
            <?php else: ?>
                <div class="vide">
                    <i class="ri-calendar-close-line"></i>
                    L'emploi du temps de votre filière n'est pas encore disponible.
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- COURS -->
    <section class="panneau" id="panneau-cours">
        <h1 class="titre-page"><i class="ri-book-open-line"></i> Cours</h1>
        <p class="sous-titre">Supports de cours de votre filière.</p>
        <div class="carte"><div class="vide"><i class="ri-book-open-line"></i>Cette rubrique sera bientôt disponible.</div></div>
    </section>

    <!-- NOTES -->
    <section class="panneau" id="panneau-notes">
        <h1 class="titre-page"><i class="ri-file-list-3-line"></i> Notes</h1>
        <p class="sous-titre">Résultats et relevés de notes.</p>
        <div class="carte"><div class="vide"><i class="ri-file-list-3-line"></i>Cette rubrique sera bientôt disponible.</div></div>
    </section>

    <!-- DOCUMENTS -->
    <section class="panneau" id="panneau-documents">
        <h1 class="titre-page"><i class="ri-folder-line"></i> Documents</h1>
        <p class="sous-titre">Pièces déposées lors de votre inscription.</p>
        <div class="carte">
            <?php if ($documents): ?>
                <ul class="liste">
                    <?php foreach ($documents as $doc): ?>
                        <li>
                            <i class="ri-file-text-line"></i>
                            <div>
                                <a href="<?= e($doc['chemin_fichier']) ?>" target="_blank" rel="noopener"><?= e($doc['type_document']) ?></a>
                                <small><?= e($doc['nom_document']) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="vide"><i class="ri-folder-open-line"></i>Aucun document.</div>
            <?php endif; ?>
        </div>
    </section>

    <!-- NOTIFICATIONS -->
    <section class="panneau" id="panneau-notifications">
        <h1 class="titre-page"><i class="ri-notification-3-line"></i> Notifications</h1>
        <p class="sous-titre">Messages de l'établissement.</p>
        <div class="carte">
            <?php if ($notifications): ?>
                <ul class="liste">
                    <?php foreach ($notifications as $n): ?>
                        <li>
                            <i class="ri-notification-3-line"></i>
                            <div>
                                <strong><?= e($n['type_notification']) ?></strong>
                                <small><?= e($n['message']) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="vide"><i class="ri-notification-off-line"></i>Aucune notification.</div>
            <?php endif; ?>
        </div>
    </section>

</div>
</main>

<script>
(function () {
    var corps = document.body;
    var bouton = document.getElementById("bouton-lateral");
    var voile = document.getElementById("voile");
    var cle = "hemip_lateral_ouvert";

    function definir(ouvert) {
        corps.classList.toggle("lateral-ouvert", ouvert);
        bouton.setAttribute("aria-expanded", ouvert ? "true" : "false");
        try { localStorage.setItem(cle, ouvert ? "1" : "0"); } catch (e) {}
    }

    /* État initial : ouverte sur grand écran, fermée sur mobile */
    var ouvert = window.innerWidth >= 900;
    try {
        var memo = localStorage.getItem(cle);
        if (memo !== null && window.innerWidth >= 900) { ouvert = memo === "1"; }
    } catch (e) {}
    definir(ouvert);

    bouton.addEventListener("click", function () {
        definir(!corps.classList.contains("lateral-ouvert"));
    });
    voile.addEventListener("click", function () { definir(false); });

    /* Fonctionnalités de la bannière */
    var boutons = document.querySelectorAll(".fonction");
    boutons.forEach(function (b) {
        b.addEventListener("click", function () {
            boutons.forEach(function (x) { x.classList.toggle("active", x === b); });
            document.querySelectorAll(".panneau").forEach(function (p) {
                p.classList.toggle("active", p.id === "panneau-" + b.dataset.panneau);
            });
            if (window.innerWidth < 900) { definir(false); }
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    });
})();
</script>
<script src="<?= asset('assets/js/pwa.js') ?>" defer></script>
</body>
</html>