<?php
session_start();

// Conexão com MySQL
$mysqli = new mysqli("localhost", "root", "", "tornados");

if ($mysqli->connect_error) {
    die("Erro na conexão: " . $mysqli->connect_error);
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    // Consulta segura para verificar login
    $sql = "SELECT id, email, senha, cidade FROM usuarios WHERE email = ? AND senha = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $email, $senha);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Salva ID e email na sessão
        $_SESSION['id_usuario'] = $row['id'];
        $_SESSION['usuario']    = $row['email'];

        // Redireciona para prevencaoSite.php
        header("Location: prevencaoSite.php");
        exit;
    } else {
        echo "<p>Usuário ou senha inválidos.</p>";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Prevenção de Tornados</title>
  <!--<link rel="stylesheet" href="assets/css/style.css">-->
  <style>
        body {
            background-color: #1a1a1a;
            color: #f0f0f0;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        h1, h2 {
            color: #b19cd9;
        }

        form {
            background-color: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px #4b0082;
            width: 300px;
        }

        label {
            display: block;
            margin-top: 15px;
            color: #dcdcdc;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: none;
            border-radius: 5px;
            background-color: #3a3a3a;
            color: #f0f0f0;
        }

        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #6a0dad;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background-color: #4b0082;
        }

        a {
            color: #b19cd9;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        p {
            margin-top: 20px;
        }

        .mensagem {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            width: 300px;
        }

        .sucesso {
            color: #4CAF50;
            background: #2a2a2a;
        }

        .erro {
            color: #ff6b6b;
            background: #2a2a2a;
        }
    </style>

</head>
<body>
  <header class="topbar">
    <div class="container">
      <h1 class="logo">🌪️ Login - Prevenção de Tornados</h1>
    </div>
  </header>

  <main class="container">
    <form method="POST" action="index.php" class="card">
      <h2>Faça seu login</h2>
      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Senha:</label>
      <input type="password" name="senha" required>

      <button type="submit" class="btn btn-primary">Entrar</button>
    </form>

    <!-- Botão de cadastro -->
    <div class="card" style="margin-top:20px; text-align:center;">
      <p>Ainda não tem conta?</p>
      <a href="cadastro.php" class="btn btn-outline">Cadastro</a>
    </div>
  </main>
</body>
</html>
