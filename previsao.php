<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Se o usuário não estiver logado, redireciona
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

// Conexão com MySQL (caso não esteja no db.php)
$mysqli = new mysqli("localhost", "root", "", "tornados");
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Pega o ID do usuário da sessão
$id_usuario = $_SESSION['id_usuario'];

// Busca a cidade cadastrada no banco
$sql = "SELECT cidade FROM usuarios WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($cidade);
$stmt->fetch();
$stmt->close();

// Se não encontrar cidade, define como "Não definida"
if (empty($cidade)) {
    $cidade = "Não definida";
}

// Mapeamento de cidades → coordenadas
$mapaCidades = [
    'São Paulo'      => [-23.5505, -46.6333],
    'Rio de Janeiro' => [-22.9068, -43.1729],
    'Curitiba'       => [-25.4284, -49.2733],
    'Brasília'       => [-15.7939, -47.8828],
    'Porto Alegre'   => [-30.0331, -51.23],
    'New Orleans'    => [29.9511, -90.0715],
    'Nova York'      => [40.7128, -74.0060],
    'Paris'          => [48.8566, 2.3522],
    'Tóquio'         => [35.6895, 139.6917],
    'Londres'        => [51.5074, -0.1278],
    'Sydney'         => [-33.8688, 151.2093],
    'Cidade do México'=> [19.4326, -99.1332],
    'Buenos Aires'   => [-34.6037, -58.3816],
    'Toronto'        => [43.6532, -79.3832],
    'Berlim'         => [52.5200, 13.4050]
];

// Coordenadas da cidade escolhida
if (isset($mapaCidades[$cidade])) {
    $lat = $mapaCidades[$cidade][0];
    $lon = $mapaCidades[$cidade][1];
} else {
    // fallback para São Paulo
    $lat = -23.5505;
    $lon = -46.6333;
}

// Chamada à API Open-Meteo
$url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}&current_weather=true&hourly=relativehumidity_2m&timezone=auto";
$data = @file_get_contents($url);
if ($data === false) {
    die("Falha ao conectar à API Open-Meteo");
}
$json = json_decode($data, true);

// Dados atuais
$temperatura = $json['current_weather']['temperature'] ?? null;
$vento = $json['current_weather']['windspeed'] ?? null; // km/h
$umidade = $json['hourly']['relativehumidity_2m'][0] ?? null;

// Heurística simples de risco
$risco = "Baixo";
if ($vento >= 30 && $umidade >= 70) $risco = "Moderado";
if ($vento >= 50 && $umidade >= 80) $risco = "Alto";

// Salvar no MySQL
$pdo = getPDO();
$stmt = $pdo->prepare("
    INSERT INTO previsoes_tempo
    (cidade, latitude, longitude, temperatura_c, umidade, vento_kmh, risco_tornado, fonte, criado_em)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([
    $cidade, $lat, $lon, $temperatura, $umidade ?? 0, $vento, $risco, 'Open-Meteo'
]);

// Histórico da cidade atual
$hstmt = $pdo->prepare("SELECT cidade, temperatura_c, umidade, vento_kmh, risco_tornado, fonte, criado_em
                        FROM previsoes_tempo
                        WHERE cidade = ?
                        ORDER BY criado_em DESC LIMIT 20");
$hstmt->execute([$cidade]);
$historico = $hstmt->fetchAll();

// Histórico de todas as cidades
$hstmtTodas = $pdo->query("SELECT cidade, temperatura_c, umidade, vento_kmh, risco_tornado, criado_em
                           FROM previsoes_tempo
                           ORDER BY criado_em DESC LIMIT 50");
$historicoTodas = $hstmtTodas->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8"/>
  <title>Previsão do Tempo (Open-Meteo)</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
<header class="topbar">
  <div class="container">
    <h1 class="logo">🌦️ Previsão do Tempo (Open-Meteo)</h1>
    <nav class="nav">
      <a href="index.php">Início</a>
      <a href="previsao.php">Previsão</a>
      <a href="mapa.php">Mapa</a>
      <a href="passo_a_passo.html">O que Fazer</a>
    </nav>
  </div>
</header>

<main class="container">
  <section class="card">
    <h2>Condição atual — <?php echo htmlspecialchars($cidade); ?></h2>
    <p><strong>Temperatura:</strong> <?php echo $temperatura; ?> °C</p>
    <p><strong>Umidade:</strong> <?php echo $umidade ?? '—'; ?>%</p>
    <p><strong>Vento:</strong> <?php echo $vento; ?> km/h</p>
    <p><strong>Risco de tornado:</strong> <?php echo $risco; ?></p>
  </section>

  <section class="card">
    <h3>Histórico de previsões — <?php echo htmlspecialchars($cidade); ?></h3>
    <table class="table">
      <thead>
        <tr>
          <th>Data</th>
          <th>Temp (°C)</th>
          <th>Umidade (%)</th>
          <th>Vento (km/h)</th>
          <th>Risco</th>
          <th>Fonte</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($historico as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['criado_em']); ?></td>
          <td><?php echo htmlspecialchars($row['temperatura_c']); ?></td>
          <td><?php echo htmlspecialchars($row['umidade']); ?></td>
          <td><?php echo htmlspecialchars($row['vento_kmh']); ?></td>
          <td><?php echo htmlspecialchars($row['risco_tornado']); ?></td>
          <td><?php echo htmlspecialchars($row['fonte']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>

  <section class="card">
    <h3>Histórico de todas as cidades já escolhidas</h3>
    <table class="table">
      <thead>
        <tr>
          <th>Cidade</th>
          <th>Data</th>
          <th>Temp (°C)</th>
          <th>Umidade (%)</th>
          <th>Vento (km/h)</th>
          <th>Risco</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($historicoTodas as $row): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['cidade']); ?></td>
          <td><?php echo htmlspecialchars($row['criado_em']); ?></td>
          <td><?php echo htmlspecialchars($row['temperatura_c']); ?></td>
          <td><?php echo htmlspecialchars($row['umidade']); ?></td>
          <td><?php echo htmlspecialchars($row['vento_kmh']); ?></td>
          <td><?php echo htmlspecialchars($row['risco_tornado']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</main>

<footer class="footer">
  <div class="container">
    <p>&copy; 2025 Prevenção de Tornados</p>
  </div>
</footer>
</body>
</html>
