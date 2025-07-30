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
$presence = $_GET['presence'] ?? '';

// Charger les invités existants
$file = 'invites.json';
$invites = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

// Ajouter un nouvel invité
$invites[] = [
    'nom' => $nom,
    'prenom' => $prenom,
    'email' => $email,
    'invitationCouple' => $invitationCouple,
    'presence' => $presence,
    'presenceConjoint' => $presenceConjoint,
    'confirmationConjoint' => $confirmationConjoint,
];

// Enregistrer dans le fichier
file_put_contents($file, json_encode($invites, JSON_PRETTY_PRINT));

// PHPMailer configuration
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'appartox.contact@gmail.com';
    $mail->Password = getenv('SMTP_PASSWORD'); // Ne jamais mettre en dur
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('appartox.contact@gmail.com', 'Patrick et Camille');
    $mail->addAddress($email, "$prenom $nom");

    $mail->isHTML(true);
    $mail->Subject = 'Confirmation de présence - Mariage de Patrick et Camille';

    // Messages personnalisés
    if ($presence == 'oui' && $invitationCouple == 'non') {
        $body = "Bonjour $prenom, merci pour votre confirmation de présence à notre mariage ! Nous avons hâte de vous voir.";
    } elseif ($presence == 'non' && $invitationCouple == 'non') {
        $body = "Bonjour $prenom, nous sommes désolés que vous ne puissiez pas venir à notre mariage. Vous serez avec nous en pensée.";
    } elseif ($presence == 'oui' && $invitationCouple == 'oui') {
        if ($presenceConjoint == 'oui') {
            $body = "Bonjour $prenom, merci pour votre confirmation de présence ainsi que celle de votre conjoint. Nous avons hâte de vous voir tous les deux !";
        } else {
            $body = "Bonjour $prenom, merci pour votre confirmation. Nous avons noté que vous viendrez sans votre conjoint.";
        }
    } elseif ($presence == 'non' && $invitationCouple == 'oui') {
        $body = "Bonjour $prenom, nous sommes désolés que vous ne puissiez pas venir avec votre conjoint. Merci pour votre réponse.";
    } else {
        $body = "Bonjour $prenom, merci pour votre réponse.";
    }

    $mail->Body = $body;

    $mail->send();
    echo "Confirmation enregistrée et mail envoyé à l'invité.";
} catch (Exception $e) {
    echo "Le message n'a pas pu être envoyé. Erreur: {$mail->ErrorInfo}";
}

// Upload vers Google Drive
function uploadToGoogleDrive($filePath, $folderId, $credentialsPath) {
    require_once 'vendor/autoload.php';

    $client = new Google_Client();
    $client->setAuthConfig($credentialsPath);
    $client->addScope(Google_Service_Drive::DRIVE_FILE);

    $service = new Google_Service_Drive($client);

    // Chercher un fichier existant avec le même nom
    $response = $service->files->listFiles([
        'q' => "name = '" . basename($filePath) . "' and '$folderId' in parents and trashed = false",
        'fields' => 'files(id, name)',
    ]);

    if (count($response->files) > 0) {
        $existingFileId = $response->files[0]->id;

        $fileMetadata = new Google_Service_Drive_DriveFile();
        $content = file_get_contents($filePath);

        $service->files->update($existingFileId, $fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/json',
            'uploadType' => 'multipart',
        ]);
    } else {
        $fileMetadata = new Google_Service_Drive_DriveFile([
            'name' => basename($filePath),
            'parents' => [$folderId]
        ]);

        $content = file_get_contents($filePath);

        $service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => 'application/json',
            'uploadType' => 'multipart',
        ]);
    }
}

// Sauvegarder les credentials dans /tmp
$tempPath = '/tmp/credentials.json';
$credentialsJson = getenv('GOOGLE_CREDENTIALS_JSON');

if (!$credentialsJson) {
    die('Clé API Google manquante.');
}

file_put_contents($tempPath, $credentialsJson);

// Effectuer l’upload vers Drive
uploadToGoogleDrive($file, '1Uyqf39Ro-efKaA1Z48MYp3K7AZn3Bon5', $tempPath);
