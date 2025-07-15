<?php
session_start();
if (!isset($_SESSION['usuario']) || !isset($_SESSION['admin']) || !$_SESSION['admin']) {
    header('Location: dashboard.php');
    exit();
}
include 'config.php';
$logs = $conn->query("SELECT * FROM logs ORDER BY datahora DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Logs do Sistema</title>
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
        <a href="logs.php">Logs</a> |
        <a href="logout.php">Sair</a>
    </nav>
    <div class="container">
        <h2>Logs do Sistema</h2>
        <table border="1" width="100%" style="margin-top:10px;">
            <tr><th>Data/Hora</th><th>Usuário</th><th>Ação</th><th>Detalhes</th></tr>
            <?php if ($logs && $logs->num_rows > 0): ?>
                <?php while ($log = $logs->fetch_assoc()): ?>
                <tr>
                    <td><?=htmlspecialchars($log['datahora'])?></td>
                    <td><?=htmlspecialchars($log['usuario'])?></td>
                    <td><?=htmlspecialchars($log['acao'])?></td>
                    <td><?=htmlspecialchars($log['detalhes'])?></td>
                </tr>
                <?php endwhile; ?>
            <?php elseif ($logs === false): ?>
                <tr><td colspan="4" style="color:red">Erro: tabela de logs não existe ou consulta falhou.</td></tr>
            <?php else: ?>
                <tr><td colspan="4">Nenhum log registrado ainda.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>
