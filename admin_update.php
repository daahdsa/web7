<?php

header('Content-Type: text/html; charset=UTF-8');

if (
    !isset($_SERVER['PHP_AUTH_USER']) ||
    !isset($_SERVER['PHP_AUTH_PW'])
) {
    header('WWW-Authenticate: Basic realm="Admin panel"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}

$pdo = new PDO(
    "mysql:host=localhost;dbname=u82283;charset=utf8",
    "u82283",
    "7013916"
);

$stmt = $pdo->prepare("
SELECT *
FROM admins
WHERE login = ?
");

$stmt->execute([$_SERVER['PHP_AUTH_USER']]);

$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (
    !$admin ||
    !password_verify($_SERVER['PHP_AUTH_PW'], $admin['password_hash'])
) {
    header('WWW-Authenticate: Basic realm="Admin panel"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}

$id = (int)$_POST['id'];

$name = trim($_POST['full_name']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email']);
$birth = $_POST['birth_date'];
$gender = $_POST['gender'];
$bio = trim($_POST['bio']);
$contract = isset($_POST['contract']) ? 1 : 0;
$languages = $_POST['languages'] ?? [];

$stmt = $pdo->prepare("
UPDATE applications
SET
full_name = ?,
phone = ?,
email = ?,
birth_date = ?,
gender = ?,
bio = ?,
contract_accepted = ?
WHERE id = ?
");

$stmt->execute([
    $name,
    $phone,
    $email,
    $birth,
    $gender,
    $bio,
    $contract,
    $id
]);

$stmt = $pdo->prepare("
DELETE FROM application_languages
WHERE application_id = ?
");

$stmt->execute([$id]);

$stmt = $pdo->prepare("
INSERT INTO application_languages
(application_id, language_id)
VALUES (?, ?)
");

foreach ($languages as $lang) {
    $stmt->execute([$id, $lang]);
}

header('Location: admin.php');
exit();