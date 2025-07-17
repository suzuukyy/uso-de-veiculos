<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit();
}
include 'config.php';
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    $sql = "SELECT * FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($senha, $row['senha'])) {
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['admin'] = $row['admin'];
            $_SESSION['usuario_id'] = $row['id'];
            include_once 'log.php';
            registrar_log($row['usuario'], 'login', 'Login bem-sucedido');
            header('Location: dashboard.php');
            exit();
        }
    }
    $erro = 'Usuário ou senha inválidos!';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - São Francisco</title>
    <link rel="stylesheet" href="assets/estilo.css">
</head>
<body>
    <div class="container">
        <h1>São Francisco</h1>
        <h2>Login</h2>
        <?php if ($erro) echo '<p class="erro">'.$erro.'</p>'; ?>
        <form method="post">
            <input type="text" name="usuario" placeholder="Usuário" required><br>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
        </form>
    </div>
</body>
</html>
