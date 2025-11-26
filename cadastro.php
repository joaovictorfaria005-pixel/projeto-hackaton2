<?php
session_start();

// Conexão com MySQL
$mysqli = new mysqli("localhost", "root", "", "tornados");

if ($mysqli->connect_error) {
  die("Erro na conexão: " . $mysqli->connect_error);
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nome   = $_POST['nome'];
  $email  = $_POST['email'];
  $senha  = $_POST['senha'];
  $cidade = $_POST['cidade'];

  // Inserir usuário no banco
  $sql = "INSERT INTO usuarios (nome, email, senha, cidade) VALUES (?, ?, ?, ?)";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param("ssss", $nome, $email, $senha, $cidade);

  if ($stmt->execute()) {
    echo "<p>Cadastro realizado com sucesso!</p>";
    echo "<a href='index.php'>Ir para login</a>";
  } else {
    echo "<p>Erro ao cadastrar: " . $stmt->error . "</p>";
  }

  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>Cadastro - Prevenção de Tornados</title>
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
      <h1 class="logo">🌪️ Cadastro - Prevenção de Tornados</h1>
    </div>
  </header>

  <main class="container">
    <form method="POST" action="cadastro.php" class="card">
      <h2>Crie sua conta</h2>

      <label>Nome:</label>
      <input type="text" name="nome" required>

      <label>Email:</label>
      <input type="email" name="email" required>

      <label>Senha:</label>
      <input type="password" name="senha" required>

      <label>Escolha sua cidade:</label>
      <select name="cidade" required>
        <option value="São Paulo">São Paulo</option>
        <option value="Rio de Janeiro">Rio de Janeiro</option>
        <option value="Curitiba">Curitiba</option>
        <option value="Brasília">Brasília</option>
        <option value="Porto Alegre">Porto Alegre</option>
        <option value="New Orleans">New Orleans</option>
        <option value="Nova York">Nova York</option>
        <option value="Paris">Paris</option>
        <option value="Tóquio">Tóquio</option>
        <option value="Londres">Londres</option>
        <option value="Sydney">Sydney</option>
        <option value="Cidade do México">Cidade do México</option>
        <option value="Buenos Aires">Buenos Aires</option>
        <option value="Toronto">Toronto</option>
        <option value="Berlim">Berlim</option>
      </select>


      <button type="submit" class="btn btn-primary">Cadastrar</button>
    </form>

    <div class="card" style="margin-top:20px; text-align:center;">
      <p>Já tem conta?</p>
      <a href="index.php" class="btn btn-outline">Login</a>
    </div>
  </main>
</body>

</html>