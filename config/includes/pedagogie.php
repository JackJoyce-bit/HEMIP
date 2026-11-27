<?php
declare(strict_types=1);

require_once __DIR__ . '/role-guard.php';
require_once __DIR__ . '/csrf.php';

const PEDAGOGIE_NIVEAUX = ['L1', 'L2', 'L3', 'Master 1', 'Master 2'];

function pedagogie_normaliser_niveau(string $niveau): ?string
{
    $correspondances = [
        '1ere-annee' => 'L1',
        '1ère-année' => 'L1',
        '2eme-annee' => 'L2',
        '2ème-année' => 'L2',
        '3eme-annee' => 'L3',
        '3ème-année' => 'L3',
        'master-1' => 'Master 1',
        'master-2' => 'Master 2',
        'L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3',
        'Master 1' => 'Master 1', 'Master 2' => 'Master 2',
    ];
    $normalise = $correspondances[trim($niveau)] ?? null;
    return in_array($normalise, PEDAGOGIE_NIVEAUX, true) ? $normalise : null;
}

function pedagogie_valider_annee(string $annee): bool
{
    if (!preg_match('/^(\d{4})-(\d{4})$/', $annee, $match)) {
        return false;
    }
    return (int) $match[2] === (int) $match[1] + 1;
}

function pedagogie_options_annees(PDO $connexion): array
{
    $depart = 2026;
    $fin = max((int) date('Y') + 3, $depart + 3);
    $annees = [];
    for ($debut = $depart; $debut <= $fin; $debut++) {
        $annees[] = sprintf('%04d-%04d', $debut, $debut + 1);
    }

    try {
        $existantes = $connexion->query('SELECT DISTINCT annee_academique FROM classe_pedagogique ORDER BY annee_academique')->fetchAll(PDO::FETCH_COLUMN);
        $annees = array_values(array_unique(array_merge($annees, $existantes)));
        sort($annees, SORT_STRING);
    } catch (PDOException $exception) {
        // La migration n’est pas encore appliquée : conserver la première année prévue.
    }
    return $annees;
}

function pedagogie_exiger_sac(PDO $connexion): array
{
    return hemip_require_role($connexion, 'SAC');
}

function pedagogie_exiger_enseignant(PDO $connexion): array
{
    return hemip_require_role($connexion, 'Enseignant');
}

function pedagogie_exiger_etudiant(PDO $connexion): array
{
    return hemip_require_role($connexion, 'Etudiant');
}

function pedagogie_filieres(PDO $connexion): array
{
    return $connexion->query('SELECT idFiliere, code_filiere, nomFiliere FROM filiere ORDER BY nomFiliere')
        ->fetchAll(PDO::FETCH_ASSOC);
}

function pedagogie_filieres_par_id(array $filieres): array
{
    $index = [];
    foreach ($filieres as $filiere) {
        $index[(int) $filiere['idFiliere']] = $filiere;
    }
    return $index;
}

function pedagogie_ensure_class(PDO $connexion, int $idFiliere, string $niveau, string $annee): int
{
    $niveauNormalise = pedagogie_normaliser_niveau($niveau);
    if ($idFiliere < 1 || $niveauNormalise === null || !pedagogie_valider_annee($annee)) {
        throw new InvalidArgumentException('La filière, le niveau ou l’année académique est invalide.');
    }

    $filiere = $connexion->prepare('SELECT 1 FROM filiere WHERE idFiliere = ? LIMIT 1');
    $filiere->execute([$idFiliere]);
    if (!$filiere->fetchColumn()) {
        throw new InvalidArgumentException('La filière sélectionnée n’existe pas.');
    }

    $classe = $connexion->prepare(
        'INSERT INTO classe_pedagogique (id_filiere, niveau, annee_academique)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE id_classe = LAST_INSERT_ID(id_classe)'
    );
    $classe->execute([$idFiliere, $niveauNormalise, $annee]);
    return (int) $connexion->lastInsertId();
}

function pedagogie_liste_etudiants(PDO $connexion, int $idClasse): array
{
    $classe = $connexion->prepare('SELECT id_filiere, niveau, annee_academique FROM classe_pedagogique WHERE id_classe = ? LIMIT 1');
    $classe->execute([$idClasse]);
    $details = $classe->fetch(PDO::FETCH_ASSOC);
    if (!$details) {
        return [];
    }

    $query = $connexion->prepare(
        "SELECT DISTINCT c.idCandidat, c.nom, c.prenom, c.email, c.telephone, c.matricule_ancien
         FROM preinscription p
         INNER JOIN candidat c ON c.idCandidat = p.idCandidat
         WHERE p.statut = 'Validée'
           AND p.idFiliere = :filiere
           AND p.annee_academique = :annee
           AND CASE p.niveau
                 WHEN '1ere-annee' THEN 'L1' WHEN '1ère-année' THEN 'L1'
                 WHEN '2eme-annee' THEN 'L2' WHEN '2ème-année' THEN 'L2'
                 WHEN '3eme-annee' THEN 'L3' WHEN '3ème-année' THEN 'L3'
                 WHEN 'master-1' THEN 'Master 1' WHEN 'master-2' THEN 'Master 2'
                 ELSE p.niveau
               END = :niveau
         ORDER BY c.nom, c.prenom, c.idCandidat"
    );
    $query->execute([
        ':filiere' => (int) $details['id_filiere'],
        ':annee' => $details['annee_academique'],
        ':niveau' => $details['niveau'],
    ]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function pedagogie_affectations_enseignant(PDO $connexion, int $idEnseignant): array
{
    $query = $connexion->prepare(
        'SELECT a.id_affectation, a.id_classe, a.id_module, c.id_filiere, c.niveau,
                c.annee_academique, f.code_filiere, f.nomFiliere, m.nom_module
         FROM affectation_enseignant a
         INNER JOIN classe_pedagogique c ON c.id_classe = a.id_classe
         INNER JOIN filiere f ON f.idFiliere = c.id_filiere
         INNER JOIN module_pedagogique m ON m.id_module = a.id_module
         WHERE a.id_enseignant = ? AND a.active = 1
         ORDER BY c.annee_academique DESC, f.nomFiliere, c.niveau, m.nom_module'
    );
    $query->execute([$idEnseignant]);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function pedagogie_enseignant_autorise(PDO $connexion, int $idEnseignant, int $idClasse, ?int $idModule = null): bool
{
    $sql = 'SELECT 1 FROM affectation_enseignant WHERE id_enseignant = ? AND id_classe = ? AND active = 1';
    $parametres = [$idEnseignant, $idClasse];
    if ($idModule !== null) {
        $sql .= ' AND id_module = ?';
        $parametres[] = $idModule;
    }
    $sql .= ' LIMIT 1';
    $query = $connexion->prepare($sql);
    $query->execute($parametres);
    return (bool) $query->fetchColumn();
}

function pedagogie_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
