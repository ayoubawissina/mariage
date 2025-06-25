<?php
$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';
$email = $_GET['email'] ?? '';

// Connexion
$conn = new mysqli("localhost", "root", "", "mariage");
if ($conn->connect_error) {
    die("Erreur DB: " . $conn->connect_error);
}

// Vérifier si déjà inscrit
$check = $conn->prepare("SELECT id FROM invites WHERE courriel = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo "Cette personne est déjà confirmée.";
    exit;
}

// Insérer
$stmt = $conn->prepare("INSERT INTO invites (nom_invite, prenom, courriel) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nom, $prenom, $email);
$stmt->execute();

// Envoyer mail à l’invité
use PHPMailer\PHPMailer\PHPMailer;
require 'vendor/autoload.php';

$mail = new PHPMailer();
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
$mail->Subject = '🎉 Votre présence a été confirmée !';
$mail->Body = "<p>Merci $prenom pour ta confirmation. Ta présence est désormais officiellement validée pour notre mariage ! 💒</p>";

if ($mail->send()) {
  echo "Confirmation enregistrée et mail envoyé à l'invité.";
} else {
  echo "Erreur mail invité : " . $mail->ErrorInfo;
}
?>
