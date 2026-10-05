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
require_once "historique.php";

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

// Historique des notifications affichées sur le tableau de bord
// (pas les emails envoyés aux candidats)
$notifications = historique_lire(
    $connexion,
    (int) ($_SESSION['idAdmin'] ?? 0)
);
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

        .document-actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .document-view-btn i {
            margin-right: 6px;
        }

        .document-view-btn.telecharger {
            color: #475569;
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

        .action-btn.delete {
            background: #d73737;
            color: #ffffff;
        }

        .action-btn.delete:hover {
            background: #b02a2a;
        }

        .action-btn i {
            margin-right: 4px;
        }

        /* =========================
           HISTORIQUE DES NOTIFICATIONS
        ========================= */

        .historique-panel {
            position: fixed;
            top: 76px;
            right: 20px;
            z-index: 9000;

            width: 400px;
            max-width: calc(100vw - 40px);
            max-height: 70vh;

            display: flex;
            flex-direction: column;

            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 15px 40px rgba(15, 23, 42, 0.22);

            overflow: hidden;
        }

        .historique-entete {
            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 16px 18px;

            border-bottom: 1px solid #edf0f4;

            font-weight: 700;
            font-size: 15px;
            color: #1e293b;
        }

        .historique-entete button {
            border: none;
            background: transparent;
            font-size: 22px;
            line-height: 1;
            color: #94a3b8;
            cursor: pointer;
        }

        .historique-entete button:hover {
            color: #475569;
        }

        .historique-liste {
            overflow-y: auto;
        }

        .historique-vide {
            padding: 30px 18px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        .historique-item {
            display: flex;
            gap: 12px;

            padding: 14px 18px;

            border-bottom: 1px solid #f1f5f9;
            border-left: 4px solid #16a34a;
        }

        .historique-item:last-child {
            border-bottom: none;
        }

        .historique-item i {
            font-size: 20px;
            line-height: 1.2;
            color: #16a34a;
        }

        .historique-item .texte {
            flex: 1;
            min-width: 0;
            font-size: 13px;
            line-height: 1.45;
            color: #334155;
        }

        .historique-item .texte strong {
            display: block;
            font-size: 14px;
            color: #1e293b;
        }

        .historique-item .date {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: #94a3b8;
        }

        .historique-item.rejet { border-left-color: #ea580c; }
        .historique-item.rejet i { color: #ea580c; }

        .historique-item.erreur { border-left-color: #dc2626; }
        .historique-item.erreur i { color: #dc2626; }

        .historique-item.info { border-left-color: #2563eb; }
        .historique-item.info i { color: #2563eb; }

        @media (max-width: 450px) {
            .historique-panel { top: 66px; right: 10px; left: 10px; width: auto; }
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
                        <span>Inscriptions en attente</span>
                        <strong id="inscriptionsEnAttenteCount">0</strong>
                    </div>

                    <div class="stat-icon orange">
                        <i class="ri-time-line"></i>
                    </div>

                </div>


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

                        <option value="en attente">
                            En attente
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
                    class="action-btn delete"
                    id="deleteBtn"
                    style="display: none;"
                >
                    <i class="ri-delete-bin-line"></i>
                    <span>Effacer candidature</span>
                </button>

                <button
                    class="action-btn reject"
                    id="rejectBtn"
                >
                    <i class="ri-close-circle-line"></i>
                    <span id="rejectLabel">Refuser</span>
                </button>

                <button
                    class="action-btn accept"
                    id="acceptBtn"
                >
                    <i class="ri-checkbox-circle-line"></i>
                    <span id="acceptLabel">Accepter la candidature</span>
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
        'numeroEtudiant' => !empty($c['matricule_ancien'])
            ? $c['matricule_ancien']
            : 'Non attribué',
        'aMatricule' => !empty($c['matricule_ancien']),

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


        /* État de l'inscription d'un étudiant (section Préinscriptions) :
           - En attente : candidature acceptée, inscription pas encore décidée
           - Inscrit    : inscription acceptée (numéro étudiant attribué)
           - Réinscrit  : demande de réinscription
           - null       : n'apparaît pas dans Préinscriptions */

        function statutInscription(c) {

            if (c.typeDemande === "Réinscription") {
                return "Réinscrit";
            }

            if (c.typeDemande === "Inscription" && c.statut === "Validée") {
                return c.aMatricule ? "Inscrit" : "En attente";
            }

            return null;
        }


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

        let selectedMode = "candidature";


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

        const etat = statutInscription(candidat);

        if (etat === null) {
            return false;
        }

        const statutPreinscription = etat.toLowerCase();


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


        const statutAffiche = statutInscription(candidat);

        const classeStatut =
            statutAffiche === "En attente" ? "pending" : "accepted";


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

                <span class="status ${classeStatut}">

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
                c => c.id === id &&
                     c.typeDemande === "Inscription"
            );

        if (!candidat) {
        return;
        }

        selectedCandidate = candidat;
        selectedMode = mode;


        // =========================
        // ÉLÉMENTS DU MODAL
        // =========================

        const documentsContainer =
        document.getElementById("modalDocuments");

        const documentsTitle =
        document.getElementById("documentsTitle");

         rejectBtn =
        document.getElementById("rejectBtn");

        const acceptBtn =
        document.getElementById("acceptBtn");

        const deleteBtn =
        document.getElementById("deleteBtn");


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


        // Dans Préinscriptions, les documents ne sont visibles
        // que pour une demande d'inscription « En attente »
        // (dans Candidatures, ils sont toujours visibles)
        if (
            mode === "preinscription" &&
            statutInscription(candidat) !== "En attente"
        ) {

        documentsTitle.style.display = "none";
        documentsContainer.style.display = "none";

        } else {

        // Documents du candidat : aperçu + téléchargement
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

                const adresse =
                    "document.php?f=" +
                    encodeURIComponent(doc.chemin_fichier);

                const extension =
                    (doc.chemin_fichier.split(".").pop() || "")
                    .toLowerCase();

                const iconeDocument =
                    extension === "pdf"
                        ? "ri-file-pdf-2-line"
                        : "ri-image-line";

                element.innerHTML = `
                    <div class="document-info">

                        <i class="${iconeDocument}"></i>

                        <span class="document-nom"></span>

                    </div>

                    <div class="document-actions">

                        <a
                            href="${adresse}"
                            target="_blank"
                            rel="noopener"
                            class="document-view-btn"
                            title="Ouvrir dans une nouvelle fenêtre"
                        >
                            <i class="ri-eye-line"></i>Aperçu
                        </a>

                        <a
                            href="${adresse}&dl=1"
                            class="document-view-btn telecharger"
                            title="Télécharger le document"
                        >
                            <i class="ri-download-2-line"></i>Télécharger
                        </a>

                    </div>
                `;

                element.querySelector(".document-nom")
                    .textContent = doc.type_document;

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

        const rejectLabel =
        document.getElementById("rejectLabel");

        const acceptLabel =
        document.getElementById("acceptLabel");

        decisionNote.textContent = "";

        if (mode === "preinscription") {

        // Préinscriptions : décision sur la demande d'inscription
        const etat = statutInscription(candidat);

        rejectLabel.textContent = "Refuser l'inscription";
        acceptLabel.textContent = "Accepter l'inscription";

        // Effacer une candidature n'existe que dans Candidatures
        deleteBtn.style.display = "none";

        if (etat === "En attente") {

            rejectBtn.style.display = "";
            acceptBtn.style.display = "";

            decisionNote.textContent =
                "Candidature acceptée : cette demande d'inscription attend votre décision.";

        } else {

            rejectBtn.style.display = "none";
            acceptBtn.style.display = "none";

        }

        // =========================
        // STATUT
        // =========================

        afficherStatutModal(etat || candidat.statut);

        } else {

        // Candidatures : décision sur la candidature
        rejectLabel.textContent = "Refuser";
        acceptLabel.textContent = "Accepter la candidature";

        const dejaValidee = candidat.statut === "Validée";
        const dejaRejetee = candidat.statut === "Rejetée";

        // Une candidature refusée peut être effacée définitivement
        deleteBtn.style.display =
            dejaRejetee ? "" : "none";

        // Une candidature validée ne peut plus être refusée
        rejectBtn.style.display =
            (dejaValidee || dejaRejetee) ? "none" : "";

        // Inutile de valider une candidature déjà validée
        acceptBtn.style.display =
            dejaValidee ? "none" : "";

        if (dejaValidee) {

            decisionNote.textContent =
                "Candidature déjà validée : elle ne peut plus être refusée. " +
                "La suite se gère dans Préinscriptions.";

        } else if (dejaRejetee) {

            decisionNote.textContent =
                "Candidature refusée : vous pouvez la valider ou l'effacer définitivement.";

        }

        // =========================
        // STATUT
        // =========================

        afficherStatutModal(candidat.statut);

        }


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

            if (
                statut === "Validée" ||
                statut === "Inscrit" ||
                statut === "Réinscrit"
            ) {
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

                const inscription = selectedMode === "preinscription";

                const question = inscription
                    ? "Voulez-vous vraiment accepter l'inscription de " +
                      selectedCandidate.prenom + " " + selectedCandidate.nom +
                      " ? Un numéro étudiant lui sera attribué et envoyé par email."
                    : "Voulez-vous vraiment accepter cette candidature ?";

                if (!confirm(question)) {
                    return;
                }

                envoyerDecision(
                    selectedCandidate.id,
                    inscription ? "accepter_inscription" : "accepter"
                );

            });


        /* =========================
           REFUSER
        ========================== */

        document.getElementById("rejectBtn")
            .addEventListener("click", () => {

                if (!selectedCandidate) return;

                const inscription = selectedMode === "preinscription";

                if (!inscription && selectedCandidate.statut === "Validée") {

                    afficherToast(
                        "erreur",
                        "Action impossible",
                        "Une candidature déjà validée ne peut plus être refusée."
                    );

                    return;
                }

                const question = inscription
                    ? "Voulez-vous vraiment refuser l'inscription de " +
                      selectedCandidate.prenom + " " + selectedCandidate.nom +
                      " ? Sa candidature repassera en « Rejetée »."
                    : "Voulez-vous vraiment refuser cette candidature ?";

                if (!confirm(question)) {
                    return;
                }

                envoyerDecision(
                    selectedCandidate.id,
                    inscription ? "refuser_inscription" : "refuser"
                );

            });

        /* =========================
           EFFACER UNE CANDIDATURE REFUSÉE
        ========================== */

        document.getElementById("deleteBtn")
            .addEventListener("click", () => {

                if (!selectedCandidate) return;

                if (
                    selectedMode === "preinscription" ||
                    selectedCandidate.statut !== "Rejetée"
                ) {
                    return;
                }

                const question =
                    "Effacer définitivement la candidature de " +
                    selectedCandidate.prenom + " " + selectedCandidate.nom +
                    " ?\n\nSon dossier et tous ses documents seront supprimés " +
                    "de la base de données. Cette action est irréversible.";

                if (!confirm(question)) {
                    return;
                }

                envoyerDecision(selectedCandidate.id, "effacer");

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
        pageDescription.textContent = "Gérez les demandes d'inscription et consultez les étudiants inscrits et réinscrits.";

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
                    statutInscription(c) === "Inscrit"
                ).length;

            const inscriptionsEnAttente =
                candidatures.filter(c =>
                    statutInscription(c) === "En attente"
                ).length;

            const reinscrits =
                candidatures.filter(c =>
                    c.typeDemande === "Réinscription"
                ).length;


            document.getElementById("inscritsCount")
                .textContent = inscrits;

            document.getElementById("inscriptionsEnAttenteCount")
                .textContent = inscriptionsEnAttente;

            document.getElementById("reinscritsCount")
                .textContent = reinscrits;
        }

        // =========================
        // NOTIFICATIONS
        // =========================

        const boutonNotification = document.querySelector(".notification");

        const iconesHistorique = {
            succes: "ri-checkbox-circle-fill",
            rejet: "ri-close-circle-fill",
            erreur: "ri-error-warning-fill",
            info: "ri-information-fill"
        };

        // "2026-10-03 14:05:00" -> "03/10/2026 à 14:05"
        function formaterDate(texte) {

            const m = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec(texte || "");

            return m
                ? m[3] + "/" + m[2] + "/" + m[1] + " à " + m[4] + ":" + m[5]
                : (texte || "");
        }

        function fermerHistorique() {

            const panneau = document.getElementById("historiquePanel");

            if (panneau) {
                panneau.remove();
            }
        }

        boutonNotification.addEventListener("click", function (evenement) {

            evenement.stopPropagation();

            // Deuxième clic : on referme
            if (document.getElementById("historiquePanel")) {
                fermerHistorique();
                return;
            }

            const panneau = document.createElement("div");
            panneau.id = "historiquePanel";
            panneau.className = "historique-panel";

            const entete = document.createElement("div");
            entete.className = "historique-entete";

            const titre = document.createElement("span");
            titre.textContent = "Historique des notifications";

            const fermer = document.createElement("button");
            fermer.setAttribute("aria-label", "Fermer");
            fermer.innerHTML = "&times;";
            fermer.addEventListener("click", fermerHistorique);

            entete.appendChild(titre);
            entete.appendChild(fermer);
            panneau.appendChild(entete);

            const liste = document.createElement("div");
            liste.className = "historique-liste";

            if (notifications.length === 0) {

                const vide = document.createElement("div");
                vide.className = "historique-vide";
                vide.textContent = "Aucune notification pour le moment.";
                liste.appendChild(vide);

            } else {

                notifications.forEach(function (notification) {

                    const type = iconesHistorique[notification.type]
                        ? notification.type
                        : "info";

                    const element = document.createElement("div");
                    element.className =
                        "historique-item " + (type === "succes" ? "" : type);

                    const icone = document.createElement("i");
                    icone.className = iconesHistorique[type];

                    const texte = document.createElement("div");
                    texte.className = "texte";

                    const strong = document.createElement("strong");
                    strong.textContent = notification.titre;

                    const message = document.createElement("span");
                    message.textContent = notification.message;

                    const date = document.createElement("span");
                    date.className = "date";
                    date.textContent = formaterDate(notification.date_creation);

                    texte.appendChild(strong);
                    texte.appendChild(message);
                    texte.appendChild(date);

                    element.appendChild(icone);
                    element.appendChild(texte);

                    liste.appendChild(element);
                });
            }

            panneau.appendChild(liste);

            document.body.appendChild(panneau);
        });

        // Clic en dehors du panneau ou touche Échap : on referme
        document.addEventListener("click", function (evenement) {

            const panneau = document.getElementById("historiquePanel");

            if (panneau && !panneau.contains(evenement.target)) {
                fermerHistorique();
            }
        });

        document.addEventListener("keydown", function (evenement) {

            if (evenement.key === "Escape") {
                fermerHistorique();
            }
        });


        /* =========================
           INITIALISATION
        ========================== */

        afficherCandidatures();

        mettreAJourStats();

        // Après une décision sur une inscription, rester dans Préinscriptions
        if (flash && flash.section === "preinscription") {
            menuPreinscription.click();
        }

    </script>

</body>
</html>