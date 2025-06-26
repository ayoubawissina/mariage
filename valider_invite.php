<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';
$email = $_GET['email'] ?? '';

if (!$nom || !$prenom || !$email) {
    die('Informations manquantes.');
}

$file = __DIR__ . '/invites.json';

// Lire les invités déjà enregistrés
if (file_exists($file)) {
    $json = file_get_contents($file);
    $invites = json_decode($json, true);
    if (!is_array($invites)) $invites = [];
} else {
    $invites = [];
}

// Vérifier doublon par email
foreach ($invites as $invite) {
    if (strtolower($invite['email']) === strtolower($email)) {
        die('Cette personne est déjà confirmée.');
    }
}

// Ajouter le nouvel invité
$invites[] = [
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'date' => date('Y-m-d H:i:s')
];

// Enregistrer dans le fichier
file_put_contents($file, json_encode($invites, JSON_PRETTY_PRINT));

// Envoyer le mail
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'patrick.mc1925@gmail.com';
    $mail->Password = 'knwitzoqyxdhijlu';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('patrick.mc1925@gmail.com', 'Camille & Patrick');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Votre présence a été confirmée !';
	$mail->Body = "
  <div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
    <p>Bonjour <strong>$prenom</strong>,</p>
    <p>Merci pour votre réponse !<br>
    Votre présence à notre mariage est désormais <strong>confirmée</strong>.</p>
    <p>Nous avons hâte de vous retrouver le <strong>13 septembre</strong> pour célébrer ensemble ce moment unique.</p>
    <p>À très bientôt !<br>
    <strong>Camille & Patrick</strong></p>
  </div>
";

    $mail->send();

    echo "Confirmation enregistrée et mail envoyé à l'invité.";

} catch (Exception $e) {
    echo "Erreur mail invité : " . $mail->ErrorInfo;
}
