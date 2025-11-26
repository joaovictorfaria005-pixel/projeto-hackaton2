<?php
session_start();
require_once __DIR__ . '/config/db.php';
$pdo = getPDO();

$action = isset($_GET['action']) ? $_GET['action'] : null;

// Mapeamento cidade → coordenadas
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

// Busca cidade do usuário SEM depender da ação
$cidadeUser = null;
$latUser = null;
$lonUser = null;

if (isset($_SESSION['id_usuario'])) {
    $mysqli = new mysqli("localhost", "root", "", "tornados");
    if (!$mysqli->connect_error) {
        $sql = "SELECT cidade FROM usuarios WHERE id = ?";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $_SESSION['id_usuario']);
        $stmt->execute();
        $stmt->bind_result($cidadeUser);
        $stmt->fetch();
        $stmt->close();

        if (!empty($cidadeUser) && isset($mapaCidades[$cidadeUser])) {
            $latUser = $mapaCidades[$cidadeUser][0];
            $lonUser = $mapaCidades[$cidadeUser][1];
        }
    }
}

// Handlers de ações
if ($action === 'list') {
    $stmt = $pdo->query("SELECT id, titulo, latitude, longitude, severidade, fonte, observado_em 
                         FROM tornados_ativos ORDER BY observado_em DESC LIMIT 200");
    $data = $stmt->fetchAll();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if ($action === 'drop_temp') {
    $pdo->exec("DROP TABLE IF EXISTS temp_tornados");
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'dropped' => 'temp_tornados']);
    exit;
}

if ($action === 'sync') {
    // Áreas padrão
    $areas = [
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

    // Se usuário tem cidade cadastrada, adiciona também
    if (!empty($cidadeUser) && !empty($latUser) && !empty($lonUser)) {
        $areas[] = [
            'nome' => $cidadeUser,
            'lat'  => $latUser,
            'lon'  => $lonUser
        ];
    }

    $inserted = 0;
    foreach ($areas as $a) {
        $url = "https://api.open-meteo.com/v1/forecast?latitude={$a['lat']}&longitude={$a['lon']}&current_weather=true";
        $data = json_decode(file_get_contents($url), true);

        if (!empty($data['current_weather'])) {
            $vento = $data['current_weather']['windspeed'];

            if ($vento >= 20) {
                $titulo = "Possível risco em {$a['nome']}";
                $severidade = ($vento >= 30) ? "Alto" : "Moderado";
                $fonte = "Open-Meteo";

                $stmt = $pdo->prepare("INSERT INTO tornados_ativos 
                    (titulo, latitude, longitude, severidade, fonte, observado_em)
                    VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$titulo, $a['lat'], $a['lon'], $severidade, $fonte]);
                $inserted++;
            }
        }
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'inserted' => $inserted]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Mapa de Tornados (Open-Meteo)</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
</head>
<body>
  <header class="topbar">
    <div class="container">
      <h1 class="logo">🌪️ Mapa de Tornados (Open-Meteo)</h1>
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
      <div class="map-toolbar">
        <button class="btn btn-primary" id="btn-sync">Sincronizar (Open-Meteo)</button>
        <button class="btn btn-outline" id="btn-refresh">Atualizar mapa</button>
        <!--<button class="btn btn-danger" id="btn-drop-temp">DROP tabela temporária</button>-->
      </div>
      <div id="map" class="map"></div>
      <p class="note">Marcadores representam áreas com vento forte detectadas pelo Open-Meteo.</p>
    </section>
  </main>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 Prevenção de Tornados</p>
    </div>
  </footer>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script>
    // Inicializa o mapa centralizado na cidade do usuário, se existir
    <?php if (!empty($latUser) && !empty($lonUser)): ?>
      var map = L.map('map').setView([<?php echo $latUser; ?>, <?php echo $lonUser; ?>], 8);
      L.marker([<?php echo $latUser; ?>, <?php echo $lonUser; ?>])
        .addTo(map)
        .bindPopup("Cidade do usuário: <?php echo htmlspecialchars($cidadeUser); ?>")
        .openPopup();
    <?php else: ?>
      var map = L.map('map').setView([-23.5505, -46.6333], 5); // fallback São Paulo
    <?php endif; ?>

    // Camada base
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
  </script>
  <script src="assets/js/mapa.js"></script>
</body>
</html>
