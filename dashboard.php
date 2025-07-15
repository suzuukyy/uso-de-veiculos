<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - São Francisco</title>
    <link rel="stylesheet" href="assets/estilo.css">
</head>
<body>
    <nav>
        <span><b>São Francisco</b></span> |
        <a href="dashboard.php">Início</a> |
        <a href="veiculos.php">Veículos</a> |
        <a href="motoristas.php">Motoristas</a> |
        <a href="uso.php">Uso de Veículos</a> |
        <a href="usuarios.php">Usuários</a> |
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
        <a href="logs.php">Logs</a> |
        <?php endif; ?>
        <a href="logout.php">Sair</a>
    </nav>
    <div class="container">
        <h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario']); ?>!</h2>
        <p>Escolha uma opção no menu acima.</p>
    </div>
</body>
</html>
