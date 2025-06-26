<?php
$file = __DIR__ . '/invites.json';

if (file_exists($file)) {
    $invites = json_decode(file_get_contents($file), true);
    if (!$invites) $invites = [];
} else {
    $invites = [];
}

foreach ($invites as $invite) {
    echo htmlspecialchars($invite['prenom']) . ' ' . htmlspecialchars($invite['nom']) . ' (' . htmlspecialchars($invite['email']) . ')<br>';
}
