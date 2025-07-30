<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$nom = $_GET['nom'] ?? '';
$prenom = $_GET['prenom'] ?? '';
$email = $_GET['email'] ?? '';
$invitationCouple = $_GET['invitationCouple'] ?? '';
$presenceConjoint = $_GET['presenceConjoint'] ?? '';
$confirmationConjoint = $_GET['confirmationConjoint'] ?? '';
$presenceInvite = $_GET['presenceInvite'] ?? '';


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
	'presenceInvite' = $presenceInvite ,
	'invitationCouple' => $invitationCouple,
    'presenceConjoint' => $presenceConjoint,
    'confirmationConjoint' => $confirmationConjoint,
    'date' => date('Y-m-d H:i:s')
];

// Personnalisation du message selon les scénarios
$messagePerso = "";

if ($presenceConjoint === 'non' && $invitationCouple === 'non') {
    if ($confirmationConjoint === 'oui') {
        // Scenario improbable (conjoint confirmé sans invitation couple), on ignore
        $messagePerso = "<p>Merci pour votre confirmation.</p>";
    } else {
        // 1. Présent et pas d'invitation couple
        if (strtolower($confirmationConjoint) === 'oui' || strtolower($confirmationConjoint) === 'non') {
            // Le seul invité
            $messagePerso = "
                <p>Bonjour <strong>$prenom</strong>,</p>
                <p>Merci d'avoir confirmé votre présence à notre mariage. Nous sommes ravis de savoir que vous serez des nôtres le <strong>19 septembre</strong>.</p>
                <p>À très bientôt !<br><strong>Camille & Patrick</strong></p>";
        }
    }
} elseif ($presenceConjoint === 'non' && $invitationCouple === 'oui') {
    // Invitation couple
    if ($confirmationConjoint === 'non' && $presenceConjoint === 'non') {
        // 5. Ni l'invité ni le conjoint ne seront présents
        $messagePerso = "
            <p>Bonjour <strong>$prenom</strong>,</p>
            <p>Nous sommes désolés d'apprendre que vous et votre conjoint(e) ne pourrez pas être présents lors de notre mariage.</p>
            <p>Nous vous remercions de nous avoir informés et espérons vous revoir bientôt.</p>
            <p><strong>Camille & Patrick</strong></p>";
    } elseif ($confirmationConjoint === 'non' && $presenceConjoint === 'oui') {
        // 4. Invité absent, conjoint présent
        $messagePerso = "
            <p>Bonjour <strong>$prenom</strong>,</p>
            <p>Merci pour votre retour. Nous avons bien noté que vous ne pourrez pas être présent, mais que votre conjoint(e) y assistera.</p>
            <p>Nous avons hâte de célébrer avec lui/elle le <strong>19 septembre</strong>.</p>
            <p><strong>Camille & Patrick</strong></p>";
    } elseif ($confirmationConjoint === 'oui' && $presenceConjoint === 'oui') {
        // 3. Invité et conjoint présents
        $messagePerso = "
            <p>Bonjour <strong>$prenom</strong>,</p>
            <p>Merci d'avoir confirmé que vous et votre conjoint(e) serez présents à notre mariage. Nous sommes impatients de partager ce moment avec vous deux le <strong>19 septembre</strong>.</p>
            <p>À très bientôt !<br><strong>Camille & Patrick</strong></p>";
    }
} elseif ($presenceConjoint === 'non' && $invitationCouple === 'non') {
    // 2. Personne ne sera pas présent (pas invitation couple)
    $messagePerso = "
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Nous avons bien pris note que vous ne pourrez pas être présent à notre mariage.</p>
        <p>Nous vous remercions de nous avoir prévenus et espérons vous revoir bientôt.</p>
        <p><strong>Camille & Patrick</strong></p>";
} else {
    // Cas par défaut / info incomplète
    $messagePerso = "
        <p>Bonjour <strong>$prenom</strong>,</p>
        <p>Merci pour votre réponse. Si vous souhaitez modifier votre confirmation, n'hésitez pas à nous contacter.</p>
        <p><strong>Camille & Patrick</strong></p>";
}


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
        $messagePerso
  </div>
";

    $mail->send();

    echo "Confirmation enregistrée et mail envoyé à l'invité.";

} catch (Exception $e) {
    echo "Erreur mail invité : " . $mail->ErrorInfo;
}


function uploadToGoogleDrive($localPath, $driveFolderId, $credentialsPath = 'credentials.json') {
    require_once 'vendor/autoload.php';

    $client = new Google_Client();
    $client->setAuthConfig($credentialsPath);
    $client->addScope(Google_Service_Drive::DRIVE);
    $service = new Google_Service_Drive($client);

    // Vérifie si le fichier existe déjà dans le dossier
    $query = "'$driveFolderId' in parents and name = 'invites.json' and trashed = false";
    $files = $service->files->listFiles(['q' => $query]);

    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => 'invites.json',
        'parents' => [$driveFolderId]
    ]);

    $content = file_get_contents($localPath);

    if (count($files->getFiles()) > 0) {
        // Mettre à jour le fichier existant
        $fileId = $files->getFiles()[0]->getId();
        $service->files->update($fileId, $fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/json',
            'uploadType' => 'multipart'
        ]);
    } else {
        // Créer un nouveau fichier
        $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/json',
            'uploadType' => 'multipart'
        ]);
    }
}
// Enregistrer dans le fichier local
file_put_contents($file, json_encode($invites, JSON_PRETTY_PRINT));

// Sauvegarder sur Google Drive
uploadToGoogleDrive($file, '1Uyqf39Ro-efKaA1Z48MYp3K7AZn3Bon5', __DIR__ . '/credentials.json');
