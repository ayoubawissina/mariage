<?php
require 'connexion_db.php';

try {
    $stmt = $conn->query("SELECT * FROM public.invite ORDER BY date_reponse DESC");
    $invites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des invités</title>
  <style>
    body { font-family: Arial; padding: 30px; background-color: #fdf6f6; }
    h1 { color: #800000; text-align: center; margin-bottom: 30px; }
    table { border-collapse: collapse; width: 100%; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    th, td { border: 1px solid #ddd; padding: 12px 15px; text-align: left; }
    th { background-color: #800000; color: white; }
    tr:nth-child(even) { background-color: #f9f2f2; }
    tr:hover { background-color: #f1e4e4; }
  </style>
</head>
<body>
  <h1>📋 Liste des invités confirmés</h1>

  <?php if (count($invites) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Prénom</th>
          <th>Nom</th>
          <th>Email</th>
          <th>Présence</th>
          <th>Invitation Couple</th>
          <th>Présence Conjoint</th>
          <th>Date de confirmation</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invites as $invite): ?>
          <tr>
            <td><?= htmlspecialchars($invite['prenom']) ?></td>
            <td><?= htmlspecialchars($invite['nom']) ?></td>
            <td><?= htmlspecialchars($invite['email']) ?></td>
            <td><?= ucfirst(htmlspecialchars($invite['presence_invite'])) ?></td>
            <td><?= ucfirst(htmlspecialchars($invite['invitation_couple'])) ?></td>
            <td><?= ucfirst(htmlspecialchars($invite['presence_conjoint'] ?? '-')) ?></td>
            <td><?= htmlspecialchars($invite['date_reponse']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:center; color:#800000;">Aucun invité confirmé pour le moment.</p>
  <?php endif; ?>
</body>
</html>
