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
    // Vérifie si l'e-mail existe déjà
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM public.invite WHERE email = :email");
    $checkStmt->execute([':email' => $email]);
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo "Cet invité a déjà confirmé sa présence.";
        exit;
    }

    // S'il n'existe pas, on insère
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
        ':presenceInvite' => $presenceInvite,
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
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
    <p>Bonjour <strong>$prenom</strong>,</p>
    <p>Merci pour votre réponse !<br>
    Votre présence à notre mariage est désormais <strong>confirmée</strong>.</p>
    <p>Nous avons hâte de vous retrouver le <strong>19 septembre</strong> pour célébrer ensemble ce moment unique.</p>
    <p>À très bientôt !<br>
    <strong>Camille & Patrick</strong></p>
  </div>";
  
} elseif ($presenceInvite == 'non' && $invitationCouple == 'non') {
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Nous sommes désolés que tu ne puisses pas venir à notre mariage.</p>
        <p>Tu seras avec nous en pensée 💌</p>
        <p>À bientôt,<br><strong>Camille & Patrick ❤️</strong></p>
    </div>";


} elseif ($presenceInvite == 'oui' && $invitationCouple == 'oui' && $presenceConjoint == 'non') {
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Nous avons bien reçu ta réponse et nous sommes heureux que tu sois des nôtres pour notre mariage ! 🎊</p>
        <p>Nous avons bien noté que tu viendras sans ton/ta conjoint(e), et nous avons hâte de te voir le <strong>19 septembre</strong> pour célébrer ensemble ce moment unique.</p>
        <p>Merci de venir célébrer avec nous 🥂</p>
        <p>À très bientôt !<br><strong>Camille & Patrick ❤️</strong></p>
    </div>";


} elseif ($presenceInvite == 'oui' && $invitationCouple == 'oui' && $presenceConjoint == 'oui') {
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Merci d'avoir confirmé votre présence à notre mariage. Nous sommes ravis de vous compter parmi nos invités !</p>
        <p>Nous avons bien noté que vous serez tous les deux présents pour partager ce moment de bonheur avec nous 🥰</p>
		<p>Nous avons hâte de vous retrouver le <strong>19 septembre</strong> pour célébrer ensemble ce moment unique.</p>
        <p>À très bientôt !<br><strong>Camille & Patrick ❤️</strong></p>
    </div>";
}


elseif ($presenceInvite == 'non' && $invitationCouple == 'oui' && $presenceConjoint == 'oui') {
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Nous avons bien reçu ta réponse et nous sommes tristes que tu ne puisses pas être présent à notre mariage.</p>
        <p>Nous aurons toutefois la joie d’accueillir ton/ta conjoint(e).</p>
        <p>Nous espérons accueillir ton/ta conjoint(e) le <strong>19 septembre</strong> pour partager un beau moment ensemble.</p>
        <p>Avec toute notre amitié,<br><strong>Camille & Patrick ❤️</strong></p>
    </div>";


} else {
    $body = "<div style='font-family: Arial, sans-serif; font-size: 16px; color: #333; line-height: 1.6;'>
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Nous avons bien reçu ta réponse et nous sommes tristes que vous ne puissiez pas être parmi nous pour célébrer notre mariage.</p>
		<p>merci d’avoir pris le temps de nous répondre.</p>
        <p>Avec toute notre amitié,<br><strong>Camille & Patrick ❤️</strong></p>
    </div>";
}



    $mail->Body = $body;
	$mail->AltBody = strip_tags(str_replace(['<br>', '</p>', '<p>'], ["\n", "\n", ""], $body));

    $mail->send();

    echo "Confirmation enregistrée et mail envoyé à l'invité.";
} catch (Exception $e) {
    echo "Erreur d'envoi : {$mail->ErrorInfo}";
}
