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
$presenceInvite = $_POST['presenceInvite'] ?? '';
$messageRSVP = "";

if ($presenceInvite === 'oui' && $invitationCouple === 'non') {
    $messageRSVP = "<p>$prenom $nom sera présent au mariage. Il n'a pas reçu d'invitation de couple.</p>";
} elseif ($presenceInvite === 'non' && $invitationCouple === 'non') {
    $messageRSVP = "<p>$prenom $nom ne pourra pas être présent au mariage. Il n'a pas reçu d'invitation de couple.</p>";
} elseif ($presenceInvite === 'oui' && $invitationCouple === 'oui' && $presenceConjoint === 'oui') {
    $messageRSVP = "<p>$prenom $nom et son/sa conjoint(e) seront tous les deux présents au mariage.</p>";
} elseif ($presenceInvite === 'non' && $invitationCouple === 'oui' && $presenceConjoint === 'oui') {
    $messageRSVP = "<p>$prenom $nom ne sera pas présent, mais son/sa conjoint(e) y assistera.</p>";
} elseif ($presenceInvite === 'non' && $invitationCouple === 'oui' && $presenceConjoint === 'non') {
    $messageRSVP = "<p>Ni $prenom $nom, ni son/sa conjoint(e) ne pourront être présents au mariage.</p>";
} elseif ($presenceInvite === 'oui' && $invitationCouple === 'oui' && $presenceConjoint === 'non') {
    $messageRSVP = "<p>$prenom $nom sera présent au mariage sans son/sa conjoint(e).</p>";
} else {
    $messageRSVP = "<p>Informations incomplètes ou cas non prévu.</p>";
}


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
    . "&confirmationConjoint=" . urlencode($confirmationConjoint)
	. "&presenceInvite=" . urlencode($presenceInvite);

// 🛠️ Construction du contenu de l'email
$body = "
<h2>Confirmation RSVP</h2>
  $messageRSVP
  <ul>
    <li><strong>Courriel :</strong> $email</li>
	<li><strong>Présence :</strong> " . ucfirst($presenceInvite) . "</li>
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
