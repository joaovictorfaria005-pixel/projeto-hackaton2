<?php
session_start();

// Se o usuário não estiver logado, redireciona para login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit;
}

// Conexão com o banco de dados 
$mysqli = new mysqli("localhost", "root", "", "tornados");

// Verifica se houve erro na conexão
if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Pega o ID do usuário da sessão
$id_usuario = $_SESSION['id_usuario'];

// Consulta para buscar a cidade cadastrada do usuário
$sql = "SELECT cidade FROM usuarios WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $id_usuario); // "i" = inteiro
$stmt->execute();
$stmt->bind_result($cidade);
$stmt->fetch();
$stmt->close();

// Se não encontrar cidade, define como "Não definida"
if (empty($cidade)) {
    $cidade = "Não definida";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Prevenção de Tornados</title>
  <link rel="stylesheet" href="assets/css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <header class="topbar">
    <div class="container">
      <h1 class="logo">🌀 Prevenção de Tornados</h1>
      <nav class="nav">
        <a href="index.php">Início</a>
        <a href="previsao.php">Previsão do Tempo</a>
        <a href="mapa.php">Mapa em Tempo Real</a>
        <a href="passo_a_passo.html">O que Fazer</a>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="container hero-grid">
      <div>
        <h2>Segurança, sinais de alerta e prevenção</h2>
        <p>
          Tornados podem se formar rapidamente e causar grandes danos. Reconheça sinais como céu escurecido,
          rotação visível nas nuvens, granizo intenso, raios frequentes e ruído semelhante a um “trem”.
          Tenha um plano familiar, um kit de emergência e saiba onde se abrigar.
        </p>
        <p><strong>Cidade selecionada:</strong> <?php echo htmlspecialchars($cidade); ?></p>
        <div class="cta-row">
          <a class="btn btn-primary" href="previsao.php?cidade=<?php echo urlencode($cidade); ?>">Ver previsão e risco</a>
          <a class="btn btn-outline" href="mapa.php">Mapa de tornados</a>
          <a class="btn btn-danger" href="passo_a_passo.html">Passo a passo de ação</a>
        </div>
      </div>
      <div class="hero-card">
        <h3>Sinais de alerta</h3>
        <ul class="checklist">
          <li><strong>Céu:</strong> coloração esverdeada ou escurecimento rápido</li>
          <li><strong>Nuvens:</strong> rotação e funil descendo</li>
          <li><strong>Vento:</strong> rajadas súbitas e mudanças bruscas de direção</li>
          <li><strong>Som:</strong> ruído contínuo forte, tipo “trem”</li>
          <li><strong>Alertas:</strong> acompanhe fontes confiáveis e rádios de emergência</li>
        </ul>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <p>&copy; 2025 Prevenção de Tornados — Informação e preparação salvam vidas.</p>
    </div>
  </footer>
</body>
</html>
