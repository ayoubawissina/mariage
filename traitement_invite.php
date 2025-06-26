<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Chemin vers le dossier où se trouve PHPMailer (via Composer)

// Paramètres de la base de données
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mariage";

// Récupération des données
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';

// Valider
if (empty($nom) || empty($prenom) || empty($email)) {
  echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
  exit;
}

// Créer un lien de validation
$token = bin2hex(random_bytes(16));
$siteURL = "https://mariage-8wp3.onrender.com"; // ← Ton vrai domaine
$validationLink = "$siteURL/valider_invite.php?token=$token&nom=" . urlencode($nom) . "&prenom=" . urlencode($prenom) . "&email=" . urlencode($email);

// Envoyer un mail au marié
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

try {
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com'; // ou autre serveur SMTP
  $mail->SMTPAuth   = true;
  $mail->Username   = 'patrick.mc1925@gmail.com';
  $mail->Password   = 'knwitzoqyxdhijlu';
  $mail->SMTPSecure = 'tls';
  $mail->Port       = 587;

  $mail->setFrom('patrick.mc1925@gmail.com', 'Confirmation Mariage');
  $mail->addAddress('patrick.mc1925@gmail.com'); // Email du marié

  $mail->isHTML(true);
  $mail->Subject = 'Nouvelle confirmation de présence';
  $mail->Body    = "
    <h2>Nouveau RSVP</h2>
    <p><strong>$prenom $nom</strong> a confirmé sa présence au mariage.</p>
    <p><a href='$validationLink' style='
      display:inline-block;
      padding:10px 15px;
      background:#28a745;
      color:white;
      text-decoration:none;
      border-radius:5px;'>Valider la présence</a></p>
  ";

  $mail->send();
  echo json_encode(['success' => true]);

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
?>
