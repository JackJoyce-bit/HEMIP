<?php
declare(strict_types=1);

/**
 * Test d'intégration qui crée un schéma dans une base MySQL/MariaDB vide
 * dédiée aux tests. Ne jamais configurer ce test avec la base de l'application.
 *
 * Variables requises :
 *   HEMIP_TEST_DSN=mysql:host=127.0.0.1;dbname=hemip_test_pedagogie;charset=utf8mb4
 *   HEMIP_TEST_DB_USER=...
 *   HEMIP_TEST_DB_PASSWORD=... (peut être vide sous XAMPP)
 */

function failTest(string $message): never
{
    fwrite(STDERR, "ÉCHEC : {$message}" . PHP_EOL);
    exit(1);
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        failTest($message);
    }
}

function runSqlFile(PDO $pdo, string $path): void
{
    $sql = file_get_contents($path);
    if ($sql === false) {
        failTest("Lecture impossible du fichier SQL : {$path}");
    }

    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    foreach (explode(';', (string) $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') {
            $pdo->exec($statement);
        }
    }
}

function expectConstraintFailure(PDO $pdo, callable $operation, string $message): void
{
    try {
        $operation();
    } catch (PDOException $exception) {
        if (($exception->errorInfo[0] ?? $exception->getCode()) === '23000') {
            return;
        }
        throw $exception;
    }

    failTest($message);
}

$dsn = getenv('HEMIP_TEST_DSN');
$dbUser = getenv('HEMIP_TEST_DB_USER');
$dbPassword = getenv('HEMIP_TEST_DB_PASSWORD');

if (!$dsn || !str_starts_with($dsn, 'mysql:')) {
    failTest('Définir HEMIP_TEST_DSN avec un DSN mysql: vers une base dédiée vide.');
}
if ($dbUser === false) {
    failTest('Définir HEMIP_TEST_DB_USER.');
}
if ($dbPassword === false) {
    $dbPassword = '';
}
if (!in_array('mysql', PDO::getAvailableDrivers(), true)) {
    failTest('Le pilote PDO MySQL n’est pas installé.');
}

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    failTest('Connexion à la base de test impossible : ' . $exception->getMessage());
}

$databaseName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if (!preg_match('/^hemip_test_[a-z0-9_]+$/', $databaseName)) {
    failTest('Sécurité : le nom de base doit commencer par hemip_test_. Aucune requête de test exécutée.');
}

$existingTables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
if ($existingTables !== []) {
    failTest('La base dédiée doit être vide ; tables détectées : ' . implode(', ', $existingTables));
}

$root = dirname(__DIR__, 2);
runSqlFile($pdo, $root . '/tests/pedagogie/fixtures.sql');
runSqlFile($pdo, $root . '/migrations/2026_11_27_pedagogie.sql');

$admins = $pdo->query('SELECT `idAdmin`,`role` FROM `administrateur` ORDER BY `idAdmin`')->fetchAll();
assertTrue(count($admins) === 2, 'La migration doit conserver les deux administrateurs synthétiques.');
assertTrue($admins[0]['role'] === 'SuperAdmin' && $admins[1]['role'] === 'Administrateur', 'Les rôles préexistants doivent être conservés sans attribution automatique de SAC/DAAC.');
assertTrue((int) $pdo->query('SELECT COUNT(*) FROM `filiere`')->fetchColumn() === 1, 'La filière synthétique doit être conservée.');

$expectedTables = ['enseignant', 'classe_pedagogique', 'module_pedagogique', 'affectation_enseignant'];
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach ($expectedTables as $table) {
    assertTrue(in_array($table, $tables, true), "Table attendue absente : {$table}");
}

$forcePasswordColumn = $pdo->query("SHOW COLUMNS FROM `enseignant` LIKE 'force_changement_mdp'")->fetch();
assertTrue($forcePasswordColumn !== false && (string) $forcePasswordColumn['Default'] === '0', 'Les comptes enseignants doivent pouvoir imposer le changement du mot de passe temporaire.');

$roleColumn = $pdo->query("SHOW COLUMNS FROM `administrateur` LIKE 'role'")->fetch();
assertTrue($roleColumn !== false && str_contains((string) $roleColumn['Type'], "'SAC'") && str_contains((string) $roleColumn['Type'], "'DAAC'"), 'Les rôles SAC et DAAC doivent être admis par le schéma.');
$pdo->exec("INSERT INTO `administrateur` (`nom`,`prenom`,`email`,`motdepasse`,`role`) VALUES ('FIXTURE','SAC','sac.fixture@example.invalid','fixture-not-a-real-password-hash','SAC'),('FIXTURE','DAAC','daac.fixture@example.invalid','fixture-not-a-real-password-hash','DAAC')");
$defaultRole = $pdo->query("SHOW COLUMNS FROM `administrateur` LIKE 'role'")->fetch();
assertTrue($defaultRole !== false && str_contains((string) $defaultRole['Default'], 'Administrateur'), 'L’extension des rôles ne doit pas changer le rôle par défaut.');

$pdo->exec("INSERT INTO `enseignant` (`nom`,`prenom`,`email`,`motdepasse`,`force_changement_mdp`) VALUES ('ENSEIGNANT','Test','enseignant.fixture@example.invalid','fixture-not-a-real-password-hash',1)");
$teacherId = (int) $pdo->lastInsertId();
assertTrue((int) $pdo->query("SELECT `force_changement_mdp` FROM `enseignant` WHERE `id_enseignant` = {$teacherId}")->fetchColumn() === 1, 'Le marqueur temporaire doit être stockable sur un compte enseignant.');
expectConstraintFailure(
    $pdo,
    static fn() => $pdo->exec("INSERT INTO `enseignant` (`nom`,`prenom`,`email`,`motdepasse`) VALUES ('AUTRE','Test','enseignant.fixture@example.invalid','fixture-not-a-real-password-hash')"),
    'Une adresse e-mail enseignant en doublon doit être refusée.'
);
$pdo->exec("INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (901,'L1','2026-2027')");
$classId = (int) $pdo->lastInsertId();
foreach (['L2', 'L3', 'Master 1', 'Master 2'] as $level) {
    $statement = $pdo->prepare('INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (?,?,?)');
    $statement->execute([901, $level, '2026-2027']);
}
$pdo->exec("INSERT INTO `module_pedagogique` (`id_filiere`,`niveau`,`nom_module`) VALUES (901,'L1','Module fixture')");
$moduleId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO `module_pedagogique` (`id_filiere`,`niveau`,`nom_module`) VALUES (901,'L1','Second module fixture')");
$secondModuleId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO `module_pedagogique` (`id_filiere`,`niveau`,`nom_module`) VALUES (901,'L2','Module autre niveau fixture')");
$otherLevelModuleId = (int) $pdo->lastInsertId();
$pdo->exec("INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (901,'L1','2027-2028')");
$nextYearClassId = (int) $pdo->lastInsertId();

expectConstraintFailure(
    $pdo,
    static fn() => $pdo->exec("INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (901,'L1','2026-2027')"),
    'Une classe filière/niveau/année en doublon doit être refusée.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $pdo->exec("INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (901,'L2','2026/2027')"),
    'Une année académique mal formée doit être refusée.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $pdo->exec("INSERT INTO `classe_pedagogique` (`id_filiere`,`niveau`,`annee_academique`) VALUES (901,'Doctorat','2028-2029')"),
    'Un niveau non prévu doit être refusé.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $pdo->exec("INSERT INTO `module_pedagogique` (`id_filiere`,`niveau`,`nom_module`) VALUES (901,'L1','Module fixture')"),
    'Un module identique dans une même filière/niveau doit être refusé.'
);

$insertAssignment = $pdo->prepare('INSERT INTO `affectation_enseignant` (`id_enseignant`,`id_classe`,`id_module`,`id_filiere`,`niveau`) VALUES (?,?,?,?,?)');
$insertAssignment->execute([$teacherId, $classId, $moduleId, 901, 'L1']);
$insertAssignment->execute([$teacherId, $classId, $secondModuleId, 901, 'L1']);
$insertAssignment->execute([$teacherId, $nextYearClassId, $moduleId, 901, 'L1']);
expectConstraintFailure(
    $pdo,
    static fn() => $insertAssignment->execute([$teacherId, $classId, $moduleId, 901, 'L1']),
    'Un doublon enseignant/classe/module doit être refusé.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $insertAssignment->execute([$teacherId, $classId, $moduleId, 901, 'L2']),
    'Une affectation déclarant un niveau incompatible avec classe/module doit être refusée.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $insertAssignment->execute([$teacherId, $classId, $otherLevelModuleId, 901, 'L1']),
    'Un module L2 ne doit pas pouvoir être affecté à une classe L1.'
);
expectConstraintFailure(
    $pdo,
    static fn() => $insertAssignment->execute([$teacherId, $classId, $moduleId, 902, 'L1']),
    'Une affectation vers une filière inexistante doit être refusée.'
);

$assignmentCount = (int) $pdo->query('SELECT COUNT(*) FROM `affectation_enseignant`')->fetchColumn();
assertTrue($assignmentCount === 3, 'Les affectations valides multi-modules et multi-années doivent être permises.');

fwrite(STDOUT, "SUCCÈS : migration, conservation des rôles/données, niveaux/années, unicité et compatibilité des affectations vérifiés dans {$databaseName}." . PHP_EOL);
