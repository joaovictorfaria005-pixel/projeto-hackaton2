<?php
$DB_HOST = 'localhost';
$DB_NAME = 'tornados';
$DB_USER = 'root';
$DB_PASS = ''; 
$DB_CHARSET = 'utf8mb4';

function getPDO() {
    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS, $DB_CHARSET;
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $DB_USER, $DB_PASS, $options);
}


// Função utilitária para chamadas HTTP (cURL)
function http_get_json($url) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $resp = curl_exec($ch);
    if ($resp === false) {
        curl_close($ch);
        return null;
    }
    curl_close($ch);
    $json = json_decode($resp, true);
    return $json ?: null;
}
