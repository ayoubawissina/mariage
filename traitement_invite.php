<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Récupération des données
$nom = $_POST['nom'] ?? '';
$prenom = $_POST['prenom'] ?? '';
$email = $_POST['email'] ?? '';
$invitationCouple = $_POST['invitationCouple'] ?? '';
$presenceConjoint = $_POST['presenceConjoint'] ?? '';
$confirmationConjoint = isset($_POST['confirmationConjoint']) ? 'Oui' : 'Non';

// Validation
if (empty($nom) || empty($prenom) || empty($email)) {
  echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
  exit;
}

// Lien de validation
$token = bin2hex(random_bytes(16));
$siteURL = "https://mariage-8wp3.onrender.com";
$validationLink = $siteURL . "/valider_invite.php?"
    . "token=" . urlencode($token)
    . "&nom=" . urlencode($nom)
    . "&prenom=" . urlencode($prenom)
    . "&email=" . urlencode($email)
    . "&invitationCouple=" . urlencode($invitationCouple)
    . "&presenceConjoint=" . urlencode($presenceConjoint)
    . "&confirmationConjoint=" . urlencode($confirmationConjoint);

// 🛠️ Construction du contenu de l'email
$body = "
  <h2>Nouveau RSVP</h2>
  <p><strong>$prenom $nom</strong> a confirmé sa présence au mariage.</p>
  <ul>
    <li><strong>Courriel :</strong> $email</li>
    <li><strong>Invitation couple :</strong> " . ucfirst($invitationCouple) . "</li>";

if ($invitationCouple === 'oui') {
  $body .= "
    <li><strong>Présence du/de la conjoint(e) :</strong> " . ucfirst($presenceConjoint) . "</li>
    <li><strong>Confirmation :</strong> $confirmationConjoint</li>";
}

$body .= "</ul>
  <p><a href='$validationLink' style='
    display:inline-block;
    padding:10px 15px;
    background:#28a745;
    color:white;
    text-decoration:none;
    border-radius:5px;'>Valider la présence</a></p>
";

// 🔐 Envoi du courriel avec PHPMailer
$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';

try {
  $mail->isSMTP();
  $mail->Host       = 'smtp.gmail.com';
  $mail->SMTPAuth   = true;
  $mail->Username   = 'patrick.mc1925@gmail.com';
  $mail->Password   = 'knwitzoqyxdhijlu';
  $mail->SMTPSecure = 'tls';
  $mail->Port       = 587;

  $mail->setFrom('patrick.mc1925@gmail.com', 'Confirmation Mariage');
  $mail->addAddress('patrick.mc1925@gmail.com');

  $mail->isHTML(true);
  $mail->Subject = 'Nouvelle confirmation de présence';
  $mail->Body    = $body; // ✅ ici on assigne le corps construit

  $mail->send();
  echo json_encode(['success' => true]);

} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Mailer Error: ' . $mail->ErrorInfo]);
}
?>
