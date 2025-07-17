<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';
include_once 'log.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'];
    $nova_senha = $_POST['nova_senha'];
    $confirma = $_POST['confirma'];
    $usuario = $_SESSION['usuario'];
    if (!$senha_atual || !$nova_senha || !$confirma) {
        $msg = 'Preencha todos os campos!';
    } elseif ($nova_senha !== $confirma) {
        $msg = 'Nova senha e confirmação não conferem!';
    } else {
        $stmt = $conn->prepare('SELECT senha FROM usuarios WHERE usuario = ?');
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!password_verify($senha_atual, $row['senha'])) {
                $msg = 'Senha atual incorreta!';
            } else {
                $nova_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt2 = $conn->prepare('UPDATE usuarios SET senha = ? WHERE usuario = ?');
                $stmt2->bind_param('ss', $nova_hash, $usuario);
                if ($stmt2->execute()) {
                    $msg = 'Senha redefinida com sucesso!';
                    registrar_log($_SESSION['usuario'], 'redefinir_senha', 'Senha redefinida com sucesso');
                } else {
                    $msg = 'Erro ao atualizar senha.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="assets/estilo.css">
</head>
<body>
    <div class="container">
        <h2>Redefinir Senha</h2>
        <?php if ($msg) echo '<p class="erro">'.$msg.'</p>'; ?>
        <form method="post">
            <input type="password" name="senha_atual" placeholder="Senha atual" required><br>
            <input type="password" name="nova_senha" placeholder="Nova senha" required><br>
            <input type="password" name="confirma" placeholder="Confirme a nova senha" required><br>
            <button type="submit">Redefinir Senha</button>
        </form>
        <a href="dashboard.php">Voltar</a>
    </div>
</body>
</html>
