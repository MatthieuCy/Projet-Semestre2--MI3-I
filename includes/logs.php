<?php



define('LOGS_FILE', __DIR__ . '/../data/logs.json');


function enregistrer_log(string $type, string $login, string $details = ''): void {
    $logs = lire_logs();

    $logs[] = [
        'id'        => count($logs) + 1,
        'date'      => date('Y-m-d H:i:s'),
        'type'      => $type,
        'login'     => $login,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'inconnue',
        'details'   => $details,
    ];

    // On garde les 500 derniers logs maximum pour ne pas grossir indéfiniment
    if (count($logs) > 500) {
        $logs = array_slice($logs, -500);
    }

    file_put_contents(LOGS_FILE, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Retourne tous les logs (du plus récent au plus ancien)
 */
function lire_logs(): array {
    if (!file_exists(LOGS_FILE)) {
        return [];
    }
    $contenu = file_get_contents(LOGS_FILE);
    $logs = json_decode($contenu, true);
    return is_array($logs) ? array_reverse($logs) : [];
}
