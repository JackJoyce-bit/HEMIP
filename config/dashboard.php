<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (
    !isset($_SESSION['admin_connecte']) ||
    $_SESSION['admin_connecte'] !== true
) {
    header("Location: login.php");
    exit();
}

require_once "connexion.php";

// Message de résultat (validation / rejet) laissé par traitement.php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header("Location: login.php");
    exit();
}

$requete = $connexion->prepare("
    SELECT
        c.*,
        p.idPreinscription,
        p.statut AS statut_preinscription,
        p.type_demande,
        p.niveau,
        f.nomFiliere
    FROM candidat c
    LEFT JOIN preinscription p
        ON p.idCandidat = c.idCandidat
    LEFT JOIN filiere f
        ON f.idFiliere = p.idFiliere
    ORDER BY c.idCandidat DESC
");

$requete->execute();

$candidatures = $requete->fetchAll(PDO::FETCH_ASSOC);

// ==========================
// RÉCUPÉRER LES DOCUMENTS
// ==========================

$requeteDocuments = $connexion->prepare("
    SELECT
        idPreinscription,
        nom_document,
        type_document,
        chemin_fichier
    FROM document
    ORDER BY idPreinscription DESC
");

$requeteDocuments->execute();

$documents = $requeteDocuments->fetchAll(PDO::FETCH_ASSOC);

$documentsParPreinscription = [];

foreach ($documents as $document) {

    $idPreinscription = $document['idPreinscription'];

    if (!isset($documentsParPreinscription[$idPreinscription])) {
        $documentsParPreinscription[$idPreinscription] = [];
    }

    $documentsParPreinscription[$idPreinscription][] = $document;
}

// Récupérer les notifications
$requeteNotifications = $connexion->prepare("SELECT * FROM notification ORDER BY date_envoi DESC");

$requeteNotifications->execute();

$notifications = $requeteNotifications->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des candidatures | HEMIP</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Remix Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.9.1/fonts/remixicon.css" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/admin-dashboard.css">

    <style>

        .document-view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            padding: 8px 14px;

            background: #f1f5f9;
            color: #2563eb;

            border: 1px solid #dbe3ef;
            border-radius: 6px;

            text-decoration: none;

            font-size: 13px;
            font-weight: 600;

            cursor: pointer;
        }

        .document-view-btn:hover {
            background: #e2e8f0;
        }

        .documents .document {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }


        /* =========================
           NOTIFICATIONS (TOASTS)
        ========================= */

        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;

            display: flex;
            flex-direction: column;
            gap: 10px;

            max-width: calc(100vw - 40px);
        }

        .toast {
            display: flex;
            align-items: flex-start;
            gap: 12px;

            width: 360px;
            max-width: 100%;

            padding: 14px 16px;

            background: #ffffff;
            border-left: 5px solid #16a34a;
            border-radius: 10px;

            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18);

            font-size: 14px;
            color: #1e293b;

            animation: toastEntree 0.35s ease forwards;
        }

        .toast.sortie {
            animation: toastSortie 0.3s ease forwards;
        }

        .toast i.toast-icone {
            font-size: 22px;
            line-height: 1;
            color: #16a34a;
        }

        .toast .toast-texte {
            flex: 1;
            line-height: 1.45;
        }

        .toast .toast-texte strong {
            display: block;
            margin-bottom: 2px;
        }

        .toast .toast-fermer {
            border: none;
            background: transparent;
            cursor: pointer;
            font-size: 18px;
            color: #94a3b8;
            line-height: 1;
        }

        .toast .toast-fermer:hover {
            color: #475569;
        }

        .toast.erreur { border-left-color: #dc2626; }
        .toast.erreur i.toast-icone { color: #dc2626; }

        .toast.rejet { border-left-color: #ea580c; }
        .toast.rejet i.toast-icone { color: #ea580c; }

        .toast.info { border-left-color: #2563eb; }
        .toast.info i.toast-icone { color: #2563eb; }

        @keyframes toastEntree {
            from { opacity: 0; transform: translateX(40px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        @keyframes toastSortie {
            from { opacity: 1; transform: translateX(0); }
            to   { opacity: 0; transform: translateX(40px); }
        }

        .decision-note {
            margin-right: auto;
            align-self: center;

            font-size: 13px;
            color: #64748b;
        }

        @media (max-width: 450px) {
            .toast-container { top: 10px; right: 10px; left: 10px; }
            .toast { width: 100%; }
        }

    </style>
    
   <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg" style="border-radius: 20px;">
</head>

<body>

    <!-- Notifications -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar">
        <a href="index.php" style="text-decoration: none;">
            <div class="logo">
                <img src="assets/img/logo-hemip.jpg" alt="">
                <span>HEMIP</span>
            </div>
        </a>
        

        <div class="menu-title">
           <i class="ri-dashboard-line"></i>
            Menu de gestion
        </div>

        <nav class="menu">

            <a href="#" class="active" id="menuCandidatures">
                <i class="ri-file-list-3-line"></i>
                Candidatures
            </a>

            <a href="#" id="menuPreinscription">
                <i class="ri-graduation-cap-line"></i>
                Préinscriptions
            </a>

        </nav>

    </aside>


    <!-- =========================
         MAIN
    ========================== -->

    <main class="main">

        <!-- TOPBAR -->

        <div class="topbar">

            <div style="display:flex; gap:12px; align-items:flex-start;">

                <button class="mobile-menu">
                    <i class="ri-menu-line"></i>
                </button>
                
                <div class="page-title">

                    <h2>
                        Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?>
                    </h2>

                    <p>
                        Rôle : <?= htmlspecialchars($_SESSION['role']) ?>
                    </p><br>

                    <h1 id="pageTitle">Gestion des candidatures</h1>

                    <p id="pageDescription">
                        Consultez et gérez les candidatures reçues.
                    </p>

                </div>

            </div>

            <div class="profile">
                <a href="deconnexion.php" class="connexion" title="Se déconnecter">
                    <i class="ri-global-off-line"></i>
                </a> 

                <button class="notification">
                    <i class="ri-notification-3-line"></i>
                </button>

                <div class="avatar">
             <?php if (($_SESSION['role'] ?? '') === 'SuperAdmin'): ?>
                <a href="administrateurs.php" title="Gérer les administrateurs"><i class="ri-user-settings-line"></i></a>
             <?php else: ?>
                <?= htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['prenom'] ?? '?', 0, 1))) ?>
             <?php endif; ?>
                </div>

            </div>

        </div>

        <div id="candidaturesSection">

        <!-- =========================
             STATISTIQUES
        ========================== -->

        <section class="stats">

            <div class="stat-card">

                <div class="stat-info">
                    <span>Total candidatures</span>
                    <strong id="totalCount"></strong>
                </div>

                <div class="stat-icon blue">
                    <i class="ri-file-list-3-line"></i>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">
                    <span>En attente</span>
                    <strong id="pendingCount"></strong>
                </div>

                <div class="stat-icon orange">
                    <i class="ri-time-line"></i>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">
                    <span>Acceptées</span>
                    <strong id="acceptedCount"></strong>
                </div>

                <div class="stat-icon green">
                    <i class="ri-checkbox-circle-line"></i>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-info">
                    <span>Refusées</span>
                    <strong id="rejectedCount"></strong>
                </div>

                <div class="stat-icon red">
                    <i class="ri-close-circle-line"></i>
                </div>

            </div>

        </section>


        <!-- =========================
             CANDIDATURES
        ========================== -->

        <section class="content-card">

            <!-- FILTERS -->

            <div class="filters">

                <div class="search-box">

                    <i class="ri-search-line"></i>

                    <input
                        type="text"
                        id="searchInput"
                        placeholder="Rechercher un candidat..."
                    >

                </div>


               <select id="poleFilter">
    <option value="">Tous les pôles</option>
    <option value="Technique">Pôle Technique</option>
    <option value="Commercial">Pôle Commercial</option>
</select>

<select id="filiereFilter">
    <option value="">Toutes les filières</option>
</select>


                <select id="niveauFilter">

                    <option value="">Tous les niveaux</option>

                        <option value="1ère année">
                            1ère année
                        </option>

                        <option value="2ème année">
                            2ème année
                        </option>

                        <option value="3ème année">
                            3ème année
                        </option>

                        <option value="Master 1">
                            Master 1
                        </option>

                        <option value="Master 2">
                            Master 2
                        </option>

                </select>


                <select id="statusFilter">

                    <option value="">Tous les statuts</option>

                    <option value="En attente">
                        En attente
                    </option>

                    <option value="Validée">
                        Validée
                    </option>

                    <option value="Rejetée">
                        Rejetée
                    </option>

                </select>

            </div>


            <!-- TABLE -->

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>Candidat</th>

                            <th>N° candidature</th>

                            <th>Filière</th>

                            <th>Niveau</th>

                            <th>Date</th>

                            <th>Statut</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody id="candidateTable">

                        <!-- Les candidatures seront générées ici -->

                    </tbody>

                </table>

            </div>

        </section>

        </div>

        <!-- =========================
             PRÉINSCRIPTIONS
        ========================== -->

        <div id="preinscriptionSection" style="display:none;">

            <section class="stats">

                <div class="stat-card">

                    <div class="stat-info">
                        <span>Étudiants inscrits</span>
                        <strong id="inscritsCount">0</strong>
                    </div>

                    <div class="stat-icon green">
                        <i class="ri-graduation-cap-line"></i>
                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-info">
                        <span>Étudiants réinscrits</span>
                        <strong id="reinscritsCount">0</strong>
                    </div>

                    <div class="stat-icon blue">
                        <i class="ri-refresh-line"></i>
                    </div>

                </div>

            </section>


            <section class="content-card">

                <div class="filters">

                    <div class="search-box">

                        <i class="ri-search-line"></i>

                        <input
                            type="text"
                            id="preinscriptionSearchInput"
                            placeholder="Rechercher un étudiant..."
                        >

                    </div>


                    <select id="preinscriptionPoleFilter">

                        <option value="">
                            Tous les pôles
                        </option>

                        <option value="Technique">
                            Pôle Technique
                        </option>

                        <option value="Commercial">
                            Pôle Commercial
                        </option>

                    </select>


                    <select id="preinscriptionFiliereFilter">

                        <option value="">
                            Toutes les filières
                        </option>

                    </select>


                    <select id="preinscriptionNiveauFilter">

                        <option value="">
                            Tous les niveaux
                        </option>

                        <option value="1ère année">
                            1ère année
                        </option>

                        <option value="2ème année">
                            2ème année
                        </option>

                        <option value="3ème année">
                            3ème année
                        </option>

                        <option value="Master 1">
                            Master 1
                        </option>

                        <option value="Master 2">
                            Master 2
                        </option>

                    </select>


                    <select id="preinscriptionStatusFilter">

                        <option value="">
                            Tous
                        </option>

                        <option value="inscrit">
                            Inscrit
                        </option>

                        <option value="réinscrit">
                            Réinscrit
                        </option>

                    </select>

                </div>


                <div class="table-container">

                    <table>

                        <thead>

                            <tr>

                                <th>Étudiant</th>
                                <th>N° étudiant</th>
                                <th>Filière</th>
                                <th>Niveau</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody id="preinscriptionTable">

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

    </main>


    <!-- =========================
         MODAL
    ========================== -->

    <div class="modal-overlay" id="modalOverlay">

        <div class="modal">

            <div class="modal-header">

                <h2>Détails de la candidature</h2>

                <button class="close-modal" id="closeModal">
                    <i class="ri-close-line"></i>
                </button>

            </div>


            <div class="modal-body">

                <div class="profile-header">

                    <div class="big-avatar" id="modalAvatar">
                        DM
                    </div>

                    <div>

                        <h3 id="modalName">
                            Dora Maloumbi
                        </h3>

                        <p id="modalId">
                            CND-0248
                        </p>

                    </div>

                </div>


                <div class="section-title">
                    Informations du candidat
                </div>

                <div class="details-grid">

                    <div class="detail-item">
                        <span>Nom complet</span>
                        <strong id="modalFullName">
                            Dora Maloumbi
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>Email</span>
                        <strong id="modalEmail">
                            dora@email.com
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>Téléphone</span>
                        <strong id="modalPhone">
                            06 123 45 67
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>Numéro étudiant</span>
                        <strong id="modalStudentId">
                            ETU-2026-0248
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>Filière demandée</span>
                        <strong id="modalFiliere">
                            Génie Logiciel
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>Niveau</span>
                        <strong id="modalNiveau">
                            Licence 1
                        </strong>
                    </div>

                </div>


                <div class="section-title" id="documentsTitle">
                    Documents
                </div>

                <div class="documents" id="modalDocuments"></div>


                <div class="section-title">
                    Statut actuel
                </div>

                <div id="modalStatus"></div>

            </div>


            <div class="modal-footer">

                <span class="decision-note" id="decisionNote"></span>

                <button
                    class="action-btn reject"
                    id="rejectBtn"
                >
                    <i class="ri-close-circle-line"></i>
                    Refuser
                </button>

                <button
                    class="action-btn accept"
                    id="acceptBtn"
                >
                    <i class="ri-checkbox-circle-line"></i>
                    Accepter la candidature
                </button>

            </div>

        </div>

    </div>


    <!-- =========================
         JAVASCRIPT
    ========================== -->

    <script>

        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });

        /* =========================
           DONNÉES
        ========================== */

        let notifications = <?= json_encode($notifications, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        const flash = <?= json_encode($flash, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

        let candidatures = <?= json_encode(array_map(function($c) use ($documentsParPreinscription) {

    $niveau = $c['niveau'] ?? '';

    switch ($niveau) {

        case '1ere-annee':
            $niveau = '1ère année';
            break;

        case '2eme-annee':
            $niveau = '2ème année';
            break;

        case '3eme-annee':
            $niveau = '3ème année';
            break;

        case 'master-1':
            $niveau = 'Master 1';
            break;

        case 'master-2':
            $niveau = 'Master 2';
            break;

        default:
            $niveau = 'Non renseigné';
    }

    return [
        'id' => $c['codeUnique'],
        'idPreinscription' => $c['idPreinscription'],
        'typeDemande' => $c['type_demande'] ?? 'Inscription',
        'nom' => $c['nom'],
        'prenom' => $c['prenom'],
        'email' => $c['email'],
        'telephone' => $c['telephone'],
        'numeroEtudiant' => $c['matricule_ancien'] ?? 'Non renseigné',

        'filiere' => $c['nomFiliere'] ?? 'Non renseignée',

        'niveau' => $niveau,

        'date' => !empty($c['date_creation'])
            ? date('d/m/Y', strtotime($c['date_creation']))
            : 'Non renseignée',

        'statut' => $c['statut_preinscription'] ?? 'En attente',

        'documents' => $documentsParPreinscription[$c['idPreinscription']] ?? []
    ];

}, $candidatures), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

         /* =========================
        FILTRES PÔLE ET FILIÈRE
        ========================== */

        const filieresTechniques = [
            "Génie Logiciel",
            "Génie Pétrolier",
            "Réseaux et Télécommunications",
            "Maintenance Industrielle",
            "Automatisation et Informatique Industriel",
            "Electrotechnique et Electronique"
        ];

        const filieresCommerciales = [
            "Droit des Affaires et des Entreprises",
            "Logistique et Transport",
            "Banque et Finance des Assurances",
            "Commerce International et Transit",
            "Gestion Finance et Comptabilité",
            "Management des Ressources Humaines"
        ];


    /* Comparaison tolérante : sans accents, sans majuscules */

        const norm = texte =>
            (texte || "")
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase()
                .trim();


    /* Déterminer le pôle de chaque candidat */

        candidatures.forEach(candidat => {

            if (filieresTechniques.some(f => norm(f) === norm(candidat.filiere))) {

                candidat.pole = "Technique";

            } else if (filieresCommerciales.some(f => norm(f) === norm(candidat.filiere))) {

                candidat.pole = "Commercial";

            } else {

                candidat.pole = "";

            }

        });


    /* Récupérer les filtres */

        const poleFilter = document.getElementById("poleFilter");

        const filiereFilter = document.getElementById("filiereFilter");


    /* Ajouter les 12 filières dans le filtre */

        const toutesLesFilieres = [
            ...filieresTechniques,
            ...filieresCommerciales
        ];

        toutesLesFilieres.forEach(filiere => {

        const option = document.createElement("option");

        option.value = filiere;
        option.textContent = filiere;

        filiereFilter.appendChild(option);

        });


        /* =========================
           ELEMENTS
        ========================== */

        const table = document.getElementById("candidateTable");

        const searchInput =
            document.getElementById("searchInput");

        const niveauFilter =
            document.getElementById("niveauFilter");

        const statusFilter =
            document.getElementById("statusFilter");

        const modalOverlay =
            document.getElementById("modalOverlay");

        const closeModal =
            document.getElementById("closeModal");


        let selectedCandidate = null;


        /* =========================
           AFFICHER LES CANDIDATURES
        ========================== */

        function afficherCandidatures() {

            const recherche =
                searchInput.value.toLowerCase();

            const pole = 
                poleFilter.value;

            const filiere =
                filiereFilter.value;

            const niveau =
                niveauFilter.value;

            const statut =
                statusFilter.value;


            const resultats =
                candidatures.filter(candidat => {

                    if (candidat.typeDemande !== "Inscription") {
                        return false;
                    }

                    const texteRecherche =
                        `${candidat.nom} ${candidat.prenom} ${candidat.id}`
                        .toLowerCase();

                    return (

                        texteRecherche.includes(recherche)

                        &&

                        (pole === "" ||
                        candidat.pole === pole)

                        &&

                        (filiere === "" ||
                        candidat.filiere === filiere)

                        &&

                        (niveau === "" ||
                        candidat.niveau === niveau)

                        &&

                        (statut === "" ||
                        candidat.statut === statut)

                    );

                });


            table.innerHTML = "";


            if (resultats.length === 0) {

                table.innerHTML = `

                    <tr>

                        <td colspan="7"
                            style="text-align:center;padding:40px;color:#9aa2b1;">

                            <i class="ri-search-line"
                               style="font-size:30px;display:block;margin-bottom:10px;">
                            </i>

                            Aucune candidature trouvée.

                        </td>

                    </tr>

                `;

                return;
            }


            resultats.forEach(candidat => {

                const initials =
                    candidat.prenom.charAt(0) +
                    candidat.nom.charAt(0);


                let statusClass = "pending";

                if (candidat.statut === "Validée") {
                    statusClass = "accepted";
                }

                if (candidat.statut === "Rejetée") {
                    statusClass = "rejected";
                }


                const row = document.createElement("tr");


                row.innerHTML = `

                    <td>

                        <div class="candidate">

                            <div class="candidate-avatar">
                                ${initials}
                            </div>

                            <div class="candidate-name">

                                <strong>
                                    ${candidat.prenom}
                                    ${candidat.nom}
                                </strong>

                                <span>
                                    ${candidat.email}
                                </span>

                            </div>

                        </div>

                    </td>


                    <td>

                        <span class="application-id">
                            #${candidat.id}
                        </span>

                    </td>


                    <td>
                        ${candidat.filiere}
                    </td>


                    <td>
                        ${candidat.niveau}
                    </td>


                    <td>
                        ${candidat.date}
                    </td>


                    <td>

                        <span class="status ${statusClass}">

                            <span class="status-dot"></span>

                            ${candidat.statut}

                        </span>

                    </td>


                    <td>

                        <button
                            class="view-btn"
                            onclick="ouvrirCandidature('${candidat.id}')"
                            title="Voir la candidature"
                        >

                            <i class="ri-eye-line"></i>

                        </button>

                    </td>

                `;


                table.appendChild(row);

            });

        }

        /* =========================
        AFFICHER LES PRÉINSCRIPTIONS
        ========================= */

        const preinscriptionTable =
            document.getElementById("preinscriptionTable");

        const preinscriptionSearchInput =
            document.getElementById("preinscriptionSearchInput");

        const preinscriptionPoleFilter =
            document.getElementById("preinscriptionPoleFilter");

        const preinscriptionFiliereFilter =
            document.getElementById("preinscriptionFiliereFilter");

        const preinscriptionNiveauFilter =
            document.getElementById("preinscriptionNiveauFilter");

        const preinscriptionStatusFilter =
            document.getElementById("preinscriptionStatusFilter");


function afficherPreinscriptions() {

    const recherche =
        preinscriptionSearchInput.value.toLowerCase();

    const filiere =
        preinscriptionFiliereFilter.value;


    const pole =
        preinscriptionPoleFilter.value;

    const niveau =
        preinscriptionNiveauFilter.value;

    const statut =
        preinscriptionStatusFilter.value;


    const resultats = candidatures.filter(candidat => {

        const estInscrit =
            candidat.typeDemande === "Inscription" &&
            candidat.statut === "Validée";

        const estReinscrit =
            candidat.typeDemande === "Réinscription";

        if (!estInscrit && !estReinscrit) {
            return false;
        }


        const statutPreinscription =
            estReinscrit ? "réinscrit" : "inscrit";


        const texteRecherche =
            `${candidat.nom} ${candidat.prenom} ${candidat.id}`
            .toLowerCase();


        return (

            texteRecherche.includes(recherche)

            &&

            (
                pole === "" ||
                candidat.pole === pole
            )

            &&

            (
                filiere === "" ||
                norm(candidat.filiere) === norm(filiere)
            )

            &&

            (
                niveau === "" ||
                candidat.niveau === niveau
            )

            &&

            (
                statut === "" ||
                statutPreinscription === statut
            )

        );

    });


    preinscriptionTable.innerHTML = "";


    if (resultats.length === 0) {

        preinscriptionTable.innerHTML = `
            <tr>
                <td colspan="7"
                    style="text-align:center;padding:40px;color:#9aa2b1;">

                    <i class="ri-search-line"
                       style="font-size:30px;display:block;margin-bottom:10px;">
                    </i>

                    Aucun étudiant trouvé.

                </td>
            </tr>
        `;

        return;
    }


    resultats.forEach(candidat => {

        const initials =
            candidat.prenom.charAt(0) +
            candidat.nom.charAt(0);


        const estReinscrit =
            candidat.typeDemande === "Réinscription";


        const statutAffiche =
            estReinscrit ? "réinscrit" : "inscrit";


        const row =
            document.createElement("tr");


        row.innerHTML = `

            <td>

                <div class="candidate">

                    <div class="candidate-avatar">
                        ${initials}
                    </div>

                    <div class="candidate-name">

                        <strong>
                            ${candidat.prenom}
                            ${candidat.nom}
                        </strong>

                        <span>
                            ${candidat.email}
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <span class="application-id">
                    ${candidat.numeroEtudiant}
                </span>

            </td>


            <td>
                ${candidat.filiere}
            </td>


            <td>
                ${candidat.niveau}
            </td>


            <td>
                ${candidat.date}
            </td>


            <td>

                <span class="status accepted">

                    <span class="status-dot"></span>

                    ${statutAffiche}

                </span>

            </td>


            <td>

                <button
                    class="view-btn"
                    onclick="ouvrirCandidature(
                        '${candidat.idPreinscription}',
                        'preinscription'
                    )"
                    title="Voir les informations"
                >

                    <i class="ri-eye-line"></i>

                </button>

            </td>

        `;


        preinscriptionTable.appendChild(row);

    });

}

    /* =========================
    FILTRES PRÉINSCRIPTION
    ========================= */

    preinscriptionSearchInput.addEventListener(
        "input",
        afficherPreinscriptions
    );

    /* Liste des filières selon le pôle choisi */

    function remplirFilieresPreinscription() {

        const pole = preinscriptionPoleFilter.value;

        let liste = [
            ...filieresTechniques,
            ...filieresCommerciales
        ];

        if (pole === "Technique") {
            liste = filieresTechniques;
        }

        if (pole === "Commercial") {
            liste = filieresCommerciales;
        }

        preinscriptionFiliereFilter.innerHTML =
            '<option value="">Toutes les filières</option>';

        liste.forEach(filiere => {

            const option = document.createElement("option");

            option.value = filiere;
            option.textContent = filiere;

            preinscriptionFiliereFilter.appendChild(option);

        });

    }

    remplirFilieresPreinscription();


    preinscriptionPoleFilter.addEventListener(
        "change",
        () => {
            remplirFilieresPreinscription();
            afficherPreinscriptions();
        }
    );

    preinscriptionFiliereFilter.addEventListener(
        "change",
        afficherPreinscriptions
    );

    preinscriptionNiveauFilter.addEventListener(
        "change",
        afficherPreinscriptions
    );

    preinscriptionStatusFilter.addEventListener(
        "change",
        afficherPreinscriptions
    );

    // =========================
    // OUVRIR UNE CANDIDATURE
    // =========================

function ouvrirCandidature(id, mode = "candidature") {

    const candidat =
        mode === "preinscription"
            ? candidatures.find(
                c => String(c.idPreinscription) === String(id)
            )
            : candidatures.find(
                c => c.id === id
            );

    if (!candidat) {
        return;
    }

    selectedCandidate = candidat;


    // =========================
    // ÉLÉMENTS DU MODAL
    // =========================

    const documentsContainer =
        document.getElementById("modalDocuments");

    const documentsTitle =
        document.getElementById("documentsTitle");

    const rejectBtn =
        document.getElementById("rejectBtn");

    const acceptBtn =
        document.getElementById("acceptBtn");


    // =========================
    // INFORMATIONS
    // =========================

    const initials =
        candidat.prenom.charAt(0) +
        candidat.nom.charAt(0);

    document.getElementById("modalAvatar")
        .textContent = initials;

    document.getElementById("modalName")
        .textContent =
        `${candidat.prenom} ${candidat.nom}`;

    document.getElementById("modalId")
        .textContent =
        `#${candidat.id}`;

    document.getElementById("modalFullName")
        .textContent =
        `${candidat.prenom} ${candidat.nom}`;

    document.getElementById("modalEmail")
        .textContent =
        candidat.email;

    document.getElementById("modalPhone")
        .textContent =
        candidat.telephone;

    document.getElementById("modalStudentId")
        .textContent =
        candidat.numeroEtudiant;

    document.getElementById("modalFiliere")
        .textContent =
        candidat.filiere;

    document.getElementById("modalNiveau")
        .textContent =
        candidat.niveau;


    // =========================
    // DOCUMENTS
    // =========================

    documentsContainer.innerHTML = "";


    // Dans Préinscriptions :
    // aucun document n'est affiché
    if (mode === "preinscription") {

        documentsTitle.style.display = "none";
        documentsContainer.style.display = "none";

    } else {

        // Dans Candidatures :
        // les documents sont affichés
        documentsTitle.style.display = "block";
        documentsContainer.style.display = "block";


        if (
            candidat.documents &&
            candidat.documents.length > 0
        ) {

            candidat.documents.forEach(function(doc) {

                const element =
                    document.createElement("div");

                element.className = "document";

                element.innerHTML = `
                    <div class="document-info">

                        <i class="ri-file-pdf-2-line"></i>

                        <span>
                            ${doc.type_document}
                        </span>

                    </div>

                    <a
                        href="${doc.chemin_fichier}"
                        target="_blank"
                        class="document-view-btn"
                    >
                        Voir
                    </a>
                `;

                documentsContainer.appendChild(element);

            });

        } else {

            documentsContainer.innerHTML = `
                <div class="document">
                    <span>
                        Aucun document trouvé.
                    </span>
                </div>
            `;

        }

    }


    // =========================
    // BOUTONS
    // =========================

    const decisionNote =
        document.getElementById("decisionNote");

    decisionNote.textContent = "";

    if (mode === "preinscription") {

        rejectBtn.style.display = "none";
        acceptBtn.style.display = "none";

    } else {

        const dejaValidee = candidat.statut === "Validée";
        const dejaRejetee = candidat.statut === "Rejetée";

        // Une candidature validée ne peut plus être refusée
        rejectBtn.style.display =
            (dejaValidee || dejaRejetee) ? "none" : "";

        // Inutile de valider une candidature déjà validée
        acceptBtn.style.display =
            dejaValidee ? "none" : "";

        if (dejaValidee) {

            decisionNote.textContent =
                "Candidature déjà validée : elle ne peut plus être refusée.";

        } else if (dejaRejetee) {

            decisionNote.textContent =
                "Candidature refusée : vous pouvez encore la valider.";

        }

    }


    // =========================
    // STATUT
    // =========================

    afficherStatutModal(candidat.statut);


    // =========================
    // OUVRIR LE MODAL
    // =========================

    modalOverlay.classList.add("show");

}

        // =========================
        // STATUT DANS MODAL
        // ==========================

        function afficherStatutModal(statut) {

            let classe = "pending";

            if (statut === "Validée") {
                classe = "accepted";
            }

            if (statut === "Rejetée") {
                classe = "rejected";
            }


            document.getElementById("modalStatus").innerHTML = `

                <span class="status ${classe}">

                    <span class="status-dot"></span>

                    ${statut}

                </span>

            `;

        }


        // =========================
        // ACCEPTER
        // ==========================

        document.getElementById("acceptBtn")
            .addEventListener("click", () => {

                if (!selectedCandidate) return;

                if (
                    !confirm("Voulez-vous vraiment accepter cette candidature ?")
                ) {
                    return;
                }

                envoyerDecision(
                    selectedCandidate.id,
                    "accepter"
                );

            });


        /* =========================
           REFUSER
        ========================== */

        document.getElementById("rejectBtn")
            .addEventListener("click", () => {

                if (!selectedCandidate) return;

                if (selectedCandidate.statut === "Validée") {

                    afficherToast(
                        "erreur",
                        "Action impossible",
                        "Une candidature déjà validée ne peut plus être refusée."
                    );

                    return;
                }

                if (
                    !confirm("Voulez-vous vraiment refuser cette candidature ?")
                ) {
                    return;
                }

                envoyerDecision(
                    selectedCandidate.id,
                    "refuser"
                );

            });

        /* =========================
           NOTIFICATIONS (TOASTS)
        ========================== */

        function afficherToast(type, titre, message) {

            const conteneur =
                document.getElementById("toastContainer");

            const icones = {
                succes: "ri-checkbox-circle-fill",
                rejet: "ri-close-circle-fill",
                erreur: "ri-error-warning-fill",
                info: "ri-information-fill"
            };

            const toast = document.createElement("div");

            toast.className =
                "toast " + (type === "succes" ? "" : type);

            toast.setAttribute("role", "status");

            const icone = document.createElement("i");
            icone.className =
                "toast-icone " + (icones[type] || icones.info);

            const texte = document.createElement("div");
            texte.className = "toast-texte";

            const strong = document.createElement("strong");
            strong.textContent = titre;
            texte.appendChild(strong);

            if (message) {
                const span = document.createElement("span");
                span.textContent = message;
                texte.appendChild(span);
            }

            const fermer = document.createElement("button");
            fermer.className = "toast-fermer";
            fermer.setAttribute("aria-label", "Fermer");
            fermer.innerHTML = "&times;";

            toast.appendChild(icone);
            toast.appendChild(texte);
            toast.appendChild(fermer);

            conteneur.appendChild(toast);

            function retirer() {
                toast.classList.add("sortie");
                setTimeout(() => toast.remove(), 300);
            }

            fermer.addEventListener("click", retirer);
            setTimeout(retirer, 6000);
        }

        // Message laissé par traitement.php après une décision
        if (flash) {
            afficherToast(flash.type, flash.titre, flash.message);
        }


        function envoyerDecision(codeUnique, action) {

            const formulaire = document.createElement("form");

            formulaire.method = "POST";
            formulaire.action = "traitement.php";

            const champCode = document.createElement("input");

            champCode.type = "hidden";
            champCode.name = "codeUnique";
            champCode.value = codeUnique;

            formulaire.appendChild(champCode);


            const champAction = document.createElement("input");

            champAction.type = "hidden";
            champAction.name = "action";
            champAction.value = action;

            formulaire.appendChild(champAction);


            document.body.appendChild(formulaire);

            formulaire.submit();
        }

        /* =========================
           FERMER MODAL
        ========================== */

        closeModal.addEventListener("click", () => {

            modalOverlay.classList.remove("show");

        });


        modalOverlay.addEventListener("click", (e) => {

            if (e.target === modalOverlay) {

                modalOverlay.classList.remove("show");

            }

        });

        /* =========================
        MENU CANDIDATURES / PRÉINSCRIPTION
        ========================= */

        const menuCandidatures =
            document.getElementById("menuCandidatures");

        const menuPreinscription =
            document.getElementById("menuPreinscription");

        const candidaturesSection =
            document.getElementById("candidaturesSection");

        const preinscriptionSection =
            document.getElementById("preinscriptionSection");

            const pageTitle =
            document.getElementById("pageTitle");

            const pageDescription =
            document.getElementById("pageDescription");


        menuCandidatures.addEventListener("click", function(e) {

        e.preventDefault();

        candidaturesSection.style.display = "block";
        preinscriptionSection.style.display = "none";

        pageTitle.textContent = "Gestion des candidatures";
        pageDescription.textContent = "Consultez et gérez les candidatures reçues.";

        menuCandidatures.classList.add("active");
        menuPreinscription.classList.remove("active");

    });


        menuPreinscription.addEventListener("click", function(e) {

        e.preventDefault();

        candidaturesSection.style.display = "none";
        preinscriptionSection.style.display = "block";

        pageTitle.textContent = "Gestion des préinscriptions";
        pageDescription.textContent = "Consultez les étudiants inscrits et réinscrits.";

        menuPreinscription.classList.add("active");
        menuCandidatures.classList.remove("active");

        afficherPreinscriptions();

        });

        /* =========================
           FILTRES
        ========================== */

        searchInput.addEventListener(
            "input",
            afficherCandidatures
        );

        poleFilter.addEventListener(
            "change",
            afficherCandidatures
        );

        filiereFilter.addEventListener(
            "change",
            afficherCandidatures
        );

        niveauFilter.addEventListener(
            "change",
            afficherCandidatures
        );

        statusFilter.addEventListener(
            "change",
            afficherCandidatures
        );


        /* =========================
           STATISTIQUES
        ========================== */

        function mettreAJourStats() {

           const candidaturesNormales =
            candidatures.filter(
                c => c.typeDemande === "Inscription"
            );

            const total =
            candidaturesNormales.length;

            const attente =
            candidaturesNormales.filter(
                c => c.statut === "En attente"
            ).length;

            const acceptees =
            candidaturesNormales.filter(
                c => c.statut === "Validée"
            ).length;

            const refusees =
            candidaturesNormales.filter(
                c => c.statut === "Rejetée"
            ).length;


            document.getElementById("totalCount")
                .textContent = total;

            document.getElementById("pendingCount")
                .textContent = attente;

            document.getElementById("acceptedCount")
                .textContent = acceptees;

            document.getElementById("rejectedCount")
                .textContent = refusees;

            const inscrits =
                candidatures.filter(c =>
                    c.typeDemande === "Inscription" &&
                    c.statut === "Validée"
                ).length;

            const reinscrits =
                candidatures.filter(c =>
                    c.typeDemande === "Réinscription"
                ).length;


            document.getElementById("inscritsCount")
                .textContent = inscrits;

            document.getElementById("reinscritsCount")
                .textContent = reinscrits;
        }

        // =========================
        // NOTIFICATIONS
        // =========================

        const boutonNotification = document.querySelector(".notification");

        boutonNotification.addEventListener("click", function () {

            if (notifications.length === 0) {
                alert("Aucune notification.");
                return;
            }

            let message = "🔔 Historique des notifications\n\n";

            notifications.forEach(function(notification) {
                message += "• " + notification.message + "\n";
                message += "  Date : " + notification.date_envoi + "\n\n";
            });

            alert(message);
        });


        /* =========================
           INITIALISATION
        ========================== */

        afficherCandidatures();

        mettreAJourStats();

    </script>

</body>
</html>