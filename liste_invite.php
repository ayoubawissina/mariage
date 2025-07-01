<?php
$file = __DIR__ . '/invites.json';

if (file_exists($file)) {
    $invites = json_decode(file_get_contents($file), true);
    if (!is_array($invites)) $invites = [];
} else {
    $invites = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Liste des invités</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 30px;
      background-color: #fdf6f6;
    }

    h1 {
      color: #800000;
      text-align: center;
      margin-bottom: 30px;
    }

    table {
      border-collapse: collapse;
      width: 100%;
      background-color: white;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    th, td {
      border: 1px solid #ddd;
      padding: 12px 15px;
      text-align: left;
    }

    th {
      background-color: #800000;
      color: white;
    }

    tr:nth-child(even) {
      background-color: #f9f2f2;
    }

    tr:hover {
      background-color: #f1e4e4;
    }
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
          <th>Couple ?</th>
          <th>Présence du conjoint</th>
          <th>Date de confirmation</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invites as $invite): ?>
          <tr>
            <td><?= htmlspecialchars($invite['prenom'] ?? '') ?></td>
            <td><?= htmlspecialchars($invite['nom'] ?? '') ?></td>
            <td><?= htmlspecialchars($invite['email'] ?? '') ?></td>
            <td><?= ucfirst(htmlspecialchars($invite['invitationCouple'] ?? '-')) ?></td>
            <td><?= ucfirst(htmlspecialchars($invite['presenceConjoint'] ?? '-')) ?></td>
            <td><?= htmlspecialchars($invite['date'] ?? '-') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <p style="text-align:center; color:#800000;">Aucun invité confirmé pour le moment.</p>
  <?php endif; ?>
</body>
</html>
