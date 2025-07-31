<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'connexion_db.php'; // inclure la connexion PDO

// Récupération des données du formulaire
$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';
$email = $_GET['email'] ?? '';
$invitationCouple = $_GET['invitationCouple'] ?? '';
$presenceConjoint = $_GET['presenceConjoint'] ?? null;
$confirmationConjoint = $_GET['confirmationConjoint'] ?? null;
$presenceInvite = $_GET['presenceInvite'] ?? '';

$confirmationConjoint = $_GET['confirmationConjoint'] ?? null;

if ($invitationCouple === 'oui' && $confirmationConjoint === 'on') {
    $confirmationConjointBool = true;
} else {
    $confirmationConjointBool = null;  // IMPORTANT : null, pas false ni ''
}

// Insertion en base PostgreSQL
try {
    $stmt = $conn->prepare("
        INSERT INTO public.invite (
            nom, prenom, email,
            presence_invite, invitation_couple,
            presence_conjoint, confirmation_conjoint
        ) VALUES (
            :nom, :prenom, :email,
            :presenceInvite, :invitationCouple,
            :presenceConjoint, :confirmationConjoint
        )
    ");

    $stmt->execute([
    ':nom' => $nom,
    ':prenom' => $prenom,
    ':email' => $email,
    ':presenceInvite' => $presenceInvite,      // <-- ici
    ':invitationCouple' => $invitationCouple,
    ':presenceConjoint' => $presenceConjoint ?: null,
    ':confirmationConjoint' => $invitationCouple === 'oui' ? $confirmationConjointBool : null
]);

} catch (PDOException $e) {
    die("Erreur en base de données : " . $e->getMessage());
}

// Envoi du courriel de confirmation
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

    $mail->setFrom('patrick.mc1925@gmail.com', 'Patrick et Camille');
    $mail->addAddress($email, "$prenom $nom");
    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de présence - Mariage de Patrick et Camille';

    // Message personnalisé
    if ($presenceInvite == 'oui' && $invitationCouple == 'non') {
    $body = "Bonjour $prenom, merci pour votre confirmation de présence à notre mariage ! Nous avons hâte de vous voir.";
} elseif ($presenceInvite == 'non' && $invitationCouple == 'non') {
    $body = "Bonjour $prenom, nous sommes désolés que vous ne puissiez pas venir à notre mariage. Vous serez avec nous en pensée.";
} elseif ($presenceInvite == 'oui' && $invitationCouple == 'oui') {
    $body = ($presenceConjoint == 'oui') ?
        "Bonjour $prenom, merci pour votre confirmation ainsi que celle de votre conjoint. Nous avons hâte de vous voir tous les deux !" :
        "Bonjour $prenom, merci pour votre confirmation. Nous avons noté que vous viendrez sans votre conjoint.";
} elseif ($presenceInvite == 'non' && $invitationCouple == 'oui') {
    $body = "Bonjour $prenom, nous sommes désolés que vous ne puissiez pas venir avec votre conjoint. Merci pour votre réponse.";
} else {
    $body = "Bonjour $prenom, merci pour votre réponse.";
}


    $mail->Body = $body;
    $mail->send();

    echo "Confirmation enregistrée et mail envoyé à l'invité.";
} catch (Exception $e) {
    echo "Erreur d'envoi : {$mail->ErrorInfo}";
}
