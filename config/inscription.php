<?php

try {

    $connexion = new PDO(
        'mysql:host=localhost;dbname=tp_php;charset=utf8',
        'root',
        ''
    );

    $connexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

} catch (Exception $e) {

    die("Erreur de connexion : " . $e->getMessage());

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si les fichiers dépassent la limite du serveur, PHP vide $_POST et $_FILES
    if (
        empty($_POST) &&
        empty($_FILES) &&
        (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
    ) {
        die(
            "Les fichiers envoyés sont trop volumineux pour le serveur "
            . "(limite actuelle : " . ini_get('post_max_size') . " au total). "
            . "Réduisez leur taille et réessayez."
        );
    }
    
    $typeDemande = $_POST['type_demande'] ?? 'Inscription';

    // ==========================
    // RÉINSCRIPTION
    // ==========================

    if ($typeDemande === 'Réinscription') {

        $matriculeAncien = trim($_POST['matricule_ancien'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $filiere = $_POST['filiere'] ?? '';
        $niveau = $_POST['niveau'] ?? '';

        // Vérifier le matricule
        if ($matriculeAncien === '') {
            die("Veuillez renseigner votre matricule / Identifiant étudiant.");
        }

        // Rechercher l'ancien candidat
        $requeteAncien = $connexion->prepare("
            SELECT
                c.idCandidat,
                c.nom,
                c.prenom,
                c.email,
                c.matricule_ancien
            FROM candidat c
            WHERE c.matricule_ancien = :matricule_ancien
            LIMIT 1
        ");

        $requeteAncien->execute([
            ':matricule_ancien' => $matriculeAncien
        ]);

        $ancienCandidat = $requeteAncien->fetch(PDO::FETCH_ASSOC);

        // Vérifier que le candidat existe
        if (!$ancienCandidat) {
            die("Aucun ancien étudiant ne correspond à ce matricule.");
        }

        // Vérifier l'adresse e-mail
        if (strtolower($ancienCandidat['email']) !== strtolower($email)) {
            die("Le matricule et l'adresse e-mail ne correspondent pas.");
        }

        // Vérifier que le candidat a déjà été validé
        $requeteValidation = $connexion->prepare("
            SELECT idPreinscription
            FROM preinscription
            WHERE idCandidat = :idCandidat
            AND statut = 'Validée'
            ORDER BY idPreinscription DESC
            LIMIT 1
        ");

        $requeteValidation->execute([
            ':idCandidat' => $ancienCandidat['idCandidat']
        ]);

        $ancienneValidation = $requeteValidation->fetch(PDO::FETCH_ASSOC);

        if (!$ancienneValidation) {
            die("Cette réinscription est réservée aux anciens étudiants déjà validés.");
        }

        // Vérifier la filière
        $requeteFiliere = $connexion->prepare("
            SELECT idFiliere
            FROM filiere
            WHERE nomFiliere = :nomFiliere
            LIMIT 1
        ");

        $requeteFiliere->execute([
            ':nomFiliere' => $filiere
        ]);

        $filiereTrouvee = $requeteFiliere->fetch(PDO::FETCH_ASSOC);

        if (!$filiereTrouvee) {
            die("La filière sélectionnée n'existe pas.");
        }

        $idFiliere = $filiereTrouvee['idFiliere'];

        // Enregistrer la réinscription
        $requeteReinscription = $connexion->prepare("
            INSERT INTO preinscription
            (
                date_demande,
                annee_academique,
                type_demande,
                statut,
                idFiliere,
                idAdmin,
                idCandidat,
                niveau
            )
            VALUES
            (
                :date_demande,
                :annee_academique,
                :type_demande,
                :statut,
                :idFiliere,
                :idAdmin,
                :idCandidat,
                :niveau
            )
        ");

        $requeteReinscription->execute([
            ':date_demande' => date('Y-m-d'),
            ':annee_academique' => '2026-2027',
            ':type_demande' => 'Réinscription',
            ':statut' => 'Validée',
            ':idFiliere' => $idFiliere,
            ':idAdmin' => 3,
            ':idCandidat' => $ancienCandidat['idCandidat'],
            ':niveau' => $niveau
        ]);

        $idPreinscription = $connexion->lastInsertId();

        // Notification
        $requeteNotification = $connexion->prepare("
            INSERT INTO notification
            (
                type_notification,
                message,
                canal,
                id_preinscription
            )
            VALUES
            (
                :type_notification,
                :message,
                :canal,
                :id_preinscription
            )
        ");

        $requeteNotification->execute([
            ':type_notification' => 'Validation',
            ':message' => 'Votre réinscription à HEMIP a bien été enregistrée.',
            ':canal' => 'Email',
            ':id_preinscription' => $idPreinscription
        ]);

        // Message de succès
        $messageSucces = "Votre réinscription a bien été enregistrée.";
        $codeConfirmation = $matriculeAncien;
    } else {

    // ==========================
    // INFORMATIONS DU CANDIDAT
    // ==========================

    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';

    $date_naissance = $_POST['date_naissance'] ?? '';

    $motdepasse = password_hash($_POST['motdepasse'] ?? '',
        PASSWORD_DEFAULT
    );

    $sexe = $_POST['sexe'] ?? '';

    $adresse = $_POST['adresse'] ?? '';
    $filiere = $_POST['filiere'] ?? '';
    $niveau = $_POST['niveau'] ?? '';
    $matricule_ancien = $_POST['matricule_ancien'] ?? '';


    // ==========================
    // CODE UNIQUE DU CANDIDAT
    // ==========================

    $codeUnique =
        'CND-' .
        date('Y') .
        '-' .
        strtoupper(
            substr(
                bin2hex(random_bytes(4)),
                0,
                6
            )
        );

    // ==========================
    // VÉRIFIER LA FILIÈRE
    // ==========================

    $requeteFiliere = $connexion->prepare("
        SELECT idFiliere
        FROM filiere
        WHERE nomFiliere = :nomFiliere
        LIMIT 1
    ");

    $requeteFiliere->execute([
        ':nomFiliere' => $filiere
    ]);

    $filiereTrouvee = $requeteFiliere->fetch(PDO::FETCH_ASSOC);

    if (!$filiereTrouvee) {
        die("La filière sélectionnée n'existe pas.");
    }

    $idFiliere = $filiereTrouvee['idFiliere'];

    // ==========================
    // VÉRIFIER LES DOCUMENTS (avant toute écriture en base)
    // ==========================

    $documentsObligatoires = [
        'acteNaissance'     => 'Acte de naissance',
        'diplome'           => 'Diplôme',
        'photoIdentite'     => 'Photo',
        'releves'           => 'Relevé de notes',
        'certificatMedical' => 'Certificat médical',
        'assurance'         => 'Assurance'
    ];

    $extensionsAutorisees = ['pdf', 'jpg', 'jpeg', 'png'];

    foreach ($documentsObligatoires as $champ => $libelle) {

        $fichier = $_FILES[$champ] ?? null;

        if ($fichier === null || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
            die("Le document « $libelle » est manquant.");
        }

        if (
            $fichier['error'] === UPLOAD_ERR_INI_SIZE ||
            $fichier['error'] === UPLOAD_ERR_FORM_SIZE
        ) {
            die(
                "Le document « $libelle » est trop volumineux "
                . "(limite par fichier : " . ini_get('upload_max_filesize') . ")."
            );
        }

        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            die("Le document « $libelle » n'a pas pu être envoyé. Veuillez réessayer.");
        }

        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees, true)) {
            die("Le document « $libelle » doit être un fichier PDF, JPG ou PNG.");
        }
    }

    // ==========================
    // ENREGISTREMENT : tout ou rien
    // ==========================

    $connexion->beginTransaction();
    $fichiersEnregistres = [];

    try {

    // ==========================
    // ENREGISTRER LE CANDIDAT
    // ==========================

    $requete = $connexion->prepare(

        "INSERT INTO candidat
        (
            nom,
            prenom,
            date_naissance,
            email,
            motdepasse,
            telephone,
            sexe,
            adresse,
            codeUnique
        )
        VALUES
        (
            :nom,
            :prenom,
            :date_naissance,
            :email,
            :motdepasse,
            :telephone,
            :sexe,
            :adresse,
            :codeUnique
        )"

    );


    $requete->execute([

        ':nom' => $nom,
        ':prenom' => $prenom,
        ':date_naissance' => $date_naissance,
        ':email' => $email,
        ':motdepasse' => $motdepasse,
        ':telephone' => $telephone,
        ':sexe' => $sexe,
        ':adresse' => $adresse,
        ':codeUnique' => $codeUnique

    ]);

    $idCandidat = $connexion->lastInsertId();

    // Créer la préinscription

    $requetePreinscription = $connexion->prepare("
        INSERT INTO preinscription
        (
            date_demande,
            annee_academique,
            type_demande,
            statut,
            idFiliere,
            idAdmin,
            idCandidat,
            niveau
        )
        VALUES
        (
            :date_demande,
            :annee_academique,
            :type_demande,
            :statut,
            :idFiliere,
            :idAdmin,
            :idCandidat,
            :niveau
        )
    ");

    $requetePreinscription->execute([
        ':date_demande' => date('Y-m-d'),
        ':annee_academique' => '2026-2027',
        ':type_demande' => 'Inscription',
        ':statut' => 'En attente',

        // À adapter selon les données existantes
        ':idFiliere' => $idFiliere,
        ':idAdmin' => 3,

        ':idCandidat' => $idCandidat,
        ':niveau' => $niveau
        ]);

    $idPreinscription = $connexion->lastInsertId();
    
    // ==========================
    // DOSSIER 'UPLOADS'
    // ==========================

    if (!is_dir('uploads')) {

        mkdir('uploads', 0777, true);

    }

    // ==========================
    // ENREGISTRER LES 6 DOCUMENTS
    // ==========================

    $requeteDocument = $connexion->prepare(
        "INSERT INTO document
        (
            nom_document,
            type_document,
            chemin_fichier,
            codeUnique,
            idPreinscription
        )
        VALUES
        (
            :nom_document,
            :type_document,
            :chemin_fichier,
            :codeUnique,
            :idPreinscription
        )"
    );

    foreach ($documentsObligatoires as $champ => $typeDocument) {

        $fichier = $_FILES[$champ];

        $nomSur = preg_replace(
            '/[^A-Za-z0-9._-]/',
            '_',
            basename($fichier['name'])
        );

        $chemin = 'uploads/' . uniqid() . '_' . $nomSur;

        if (!move_uploaded_file($fichier['tmp_name'], $chemin)) {
            throw new RuntimeException(
                "Impossible d'enregistrer le fichier : " . $fichier['name']
            );
        }

        $fichiersEnregistres[] = $chemin;

        $requeteDocument->execute([
            ':nom_document' => $fichier['name'],
            ':type_document' => $typeDocument,
            ':chemin_fichier' => $chemin,
            ':codeUnique' => $codeUnique,
            ':idPreinscription' => $idPreinscription
        ]);
    }

    // ==========================
    // NOTIFICATION DE RÉCEPTION
    // ==========================

    $requeteNotification = $connexion->prepare("
        INSERT INTO notification
        (
            type_notification,
            message,
            canal,
            id_preinscription
        )
        VALUES
        (
            :type_notification,
            :message,
            :canal,
            :id_preinscription
        )
    ");

    $requeteNotification->execute([
        ':type_notification' => 'Réception',
        ':message' => 'Votre inscription a bien été enregistrée. Votre candidature est en cours de traitement...',
        ':canal' => 'Email',
        ':id_preinscription' => $idPreinscription
    ]);

    $connexion->commit();

    } catch (Throwable $e) {

        // Rien ne doit rester à moitié enregistré
        if ($connexion->inTransaction()) {
            $connexion->rollBack();
        }

        foreach ($fichiersEnregistres as $cheminFichier) {
            if (is_file($cheminFichier)) {
                unlink($cheminFichier);
            }
        }

        error_log("Inscription HEMIP : " . $e->getMessage());

        die(
            "Une erreur est survenue : votre inscription n'a pas été enregistrée. "
            . "Veuillez réessayer."
        );
    }


    // ==========================
    // MESSAGE DE CONFIRMATION
    // ==========================

        $messageSucces = "Votre inscription a bien été enregistrée.";
    $codeConfirmation = $codeUnique;

    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <!-- Remix Icon -->
   
    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@4.6.0/fonts/remixicon.css"
        rel="stylesheet"
    >
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/inscription.css">
     <!-- favicon -->
    <link rel="icon" type="image/png" href="assets/img/logo-hemip.jpg" style="border-radius: 20px;">

    <title>Inscription HEMIP</title>
</head>
<body>

    <?php if (isset($messageSucces)) : ?>

    <div class="message-overlay"></div>

<div class="message-succes">
    <div class="message-succes-icon">
        <i class="ri-checkbox-circle-line"></i>
    </div>

    <h2>Inscription réussie !</h2>

    <p>
        <?= htmlspecialchars($messageSucces) ?>
    </p>

    <p>
        Votre demande est maintenant
        <strong>en attente de traitement</strong>.
    </p>

    <div class="code-candidature">
        <span>Votre code de candidature</span>
        <strong><?= htmlspecialchars($codeConfirmation) ?></strong>
    </div>

    <button type="button" onclick="fermerMessageSucces()">
        Continuer
    </button>
</div>

<?php endif; ?>

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
<div class="containerpage">
<div class="containerIns">
<section>
  <div class="sec-container">
      <div class="form-wrapper">
         <div class="card">
            <div class="card-header">
              <div id="forlogin" class="form-header active"> Se réinscrire  </div>    
                 <div id="forregister" class="form-header"> S'inscrire  </div>    
           </div>
  <div class="card-body-wrapper">

    <div class="forms-slider" id="formContainer">

        <form id="loginForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="type_demande" value="Réinscription">

            <input type="text" name="nom" class="form-control" placeholder="Nom">
            <input type="text" name="prenom" class="form-control" placeholder="Prénom">
            <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="Numéro de telephone" required  >
    <input type="email"id="email"name="email"class="form-control"placeholder="Email"required >
            <div class="form-group">
    <select id="niveau" name="niveau" class="form-control" required>
        <option value="1ere-annee">1ère année</option>
        <option value="2eme-annee">2ème année</option>
        <option value="3eme-annee">3ème année</option>
        <option value="master-1">Master 1</option>
        <option value="master-2">Master 2</option>
    </select>
</div>
  
<div class="form-group">
    <select class="pole-select form-control" name="pole" required>
        <option value="">Choisissez un pôle</option>
        <option value="technique">Pôle Technique</option>
        <option value="commercial">Pôle Commercial</option>
    </select>
</div>


<div class="form-group">
    <select class="filiere-select form-control" name="filiere" required disabled>
        <option value="">Choisissez d'abord un pôle</option>
    </select>
</div>
    <input type="text" id="matriculeAncien" name="matricule_ancien" class="form-control" placeholder="Matricule / Identifiant étudiant" required>
            <button type="submit" class="formButton">Réinscription</button>
        </form>


        


        <form id="registerForm" method="POST" action="inscription.php" enctype="multipart/form-data">
    <input type="hidden" name="type_demande" value="Inscription">

          <input type="text" name="nom" class="form-control" placeholder="Nom">
            <input type="text" name="prenom" class="form-control" placeholder="Prénom">
            <input type="tel" id="telephone" name="telephone" class="form-control" placeholder="Numéro de téléphone" required  >
    <input type="email"id="email"name="email"class="form-control"placeholder="Email"required >
    <input
    type="date"
    name="date_naissance"
    class="form-control"
    required
>

<input
    type="password"
    name="motdepasse"
    class="form-control"
    placeholder="Mot de passe"
    required
>

<select name="sexe" class="form-control" required>
    <option value="">Sexe</option>
    <option value="Masculin">Masculin</option>
    <option value="Féminin">Féminin</option>
</select>

<input
    type="text"
    name="adresse"
    class="form-control"
    placeholder="Adresse"
    required
>
     <div class="form-group">
    <select id="niveau" name="niveau" class="form-control" required>
        <option value="1ere-annee">1ère année</option>
        <option value="2eme-annee">2ème année</option>
        <option value="3eme-annee">3ème année</option>
        <option value="master-1">Master 1</option>
        <option value="master-2">Master 2</option>
    </select>
<div class="form-group">
    <select class="pole-select form-control" name="pole" required>
        <option value="">Choisissez un pôle</option>
        <option value="technique">Pôle Technique</option>
        <option value="commercial">Pôle Commercial</option>
    </select>
</div>


<div class="form-group">
    <select class="filiere-select form-control" name="filiere" required disabled>
        <option value="">Choisissez d'abord un pôle</option>
    </select>
</div>

<div class="file-upload">
    <label for="acteNaissance" class="file-label">
        <i class="ri-file-text-line"></i>
        <span class="file-title">Acte ou extrait d'acte <br>de naissance</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>

    <input type="file" id="acteNaissance" name="acteNaissance"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>

<div class="file-upload">
    <label for="diplome" class="file-label">
        <i class="ri-graduation-cap-line"></i>
        <span class="file-title">Diplôme ou attestation de réussite <br>au Baccalauréat légalisée</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>

    <input type="file" id="diplome" name="diplome"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>
<div class="file-upload">
    <label for="photoIdentite" class="file-label">
        <i class="ri-graduation-cap-line"></i>
        <span class="file-title">Photo format identité<br>(avec fond blanc)</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>
    

    <input type="file" id="photoIdentite" name="photoIdentite"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>
<div class="file-upload">
    <label for="releves" class="file-label">
        <i class="ri-graduation-cap-line"></i>
        <span class="file-title">Copie légalisée des relevés de notes<br> de semestres antérieurs <br> (pour Inscription en 2em ou 3em année)</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>
    

    <input type="file" id="releves" name="releves"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>
<div class="file-upload">
    <label for="certificatMedical" class="file-label">
        <i class="ri-file-text-line"></i>
        <span class="file-title">Certficat médical <br>(Centre médical International) Boscongo</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>
    

    <input type="file" id="certificatMedical" name="certificatMedical"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>
<div class="file-upload">
    <label for="assurance" class="file-label">
        <i class="ri-file-text-line"></i>
        <span class="file-title">Attestation Assurance <br>(SUNU Assurance) Notre Dame</span>
        <span class="file-info">PDF, JPG ou PNG</span>
    </label>
    

    <input type="file" id="assurance" name="assurance"
           accept=".pdf,.jpg,.jpeg,.png" required>
</div>
            <button type="submit" class="formButton">Inscription</button>
        </form>

    </div>

</div>
    </div>
  </div>
 </div>
</section>
</div>

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

<script src="assets/js/main.js"></script>
<script>
const formlogin = document.getElementById('forlogin');
const forRegister = document.getElementById('forregister');
const formContainer = document.getElementById('formContainer');


/* S'INSCRIRE */
formlogin.addEventListener('click', () => {

    formlogin.classList.add('active');
    forRegister.classList.remove('active');

    formContainer.style.transform = 'translateX(0)';
});


/* SE RÉINSCRIRE */
forRegister.addEventListener('click', () => {

    forRegister.classList.add('active');
    formlogin.classList.remove('active');

    formContainer.style.transform = 'translateX(-50%)';
});

const formations = {

    technique: [
        "Génie Logiciel",
        "Génie Pétrolier",
        "Réseaux et Télécommunications",
        "Maintenance Industrielle",
        "Automatisation et Informatique Industriel",
        "Electrotechnique et Electronique",
    ],

    commercial: [
        "Droit des Affaires et des Entreprises",
        "Logistique et Transport",
        "Banque et Finance des Assurances",
        "Commerce International et Transit",
        "Gestion Finance et Comptabilité ",
        "Management des Ressources Humaines"
    ]

};


/* Faire fonctionner chaque formulaire indépendamment */

document.querySelectorAll('.form-group').forEach(() => {});


document.querySelectorAll('.pole-select').forEach((pole) => {

    pole.addEventListener('change', function () {

        // On cherche la formation qui appartient
        // au même formulaire
        const form = this.closest('form');

        const filiere =
            form.querySelector('.filiere-select');

        const choix = this.value;

        filiere.innerHTML =
            '<option value="">Choisissez une filière</option>';

        if (choix && formations[choix]) {

            filiere.disabled = false;

            formations[choix].forEach(function (nomFiliere) {

                const option = document.createElement('option');

                option.value = nomFiliere;
                option.textContent = nomFiliere;

                filiere.appendChild(option);

            });

        } else {

            filiere.disabled = true;

        }

    });

});

const formWrapper = document.querySelector('.card-body-wrapper');
const formsSlider = document.getElementById('formContainer');

const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');


function adjustFormHeight(form) {
    formWrapper.style.height = form.offsetHeight + 'px';
}


/* Hauteur initiale */
adjustFormHeight(loginForm);


/* Aller vers Inscription */
formlogin.addEventListener('click', () => {

    formlogin.classList.add('active');
    forRegister.classList.remove('active');

    formsSlider.style.transform = 'translateX(0)';

    adjustFormHeight(loginForm);
});


/* Aller vers Réinscription */
forRegister.addEventListener('click', () => {

    forRegister.classList.add('active');
    formlogin.classList.remove('active');

    formsSlider.style.transform = 'translateX(-50%)';

    adjustFormHeight(registerForm);
});

function fermerMessageSucces() {

    const message = document.querySelector('.message-succes');
    const overlay = document.querySelector('.message-overlay');

    if (message) {
        message.classList.add('fermeture-message');
    }

    if (overlay) {
        overlay.classList.add('fermeture-overlay');
    }

    setTimeout(function () {

        if (message) {
            message.remove();
        }

        if (overlay) {
            overlay.remove();
        }

    }, 300);
}
</script>
 </div>
</body>
</html>