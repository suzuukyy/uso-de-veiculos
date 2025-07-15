<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';
$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == '1') {
        $msg = 'Veículo cadastrado!';
    } elseif ($_GET['msg'] == '2') {
        $msg = 'Veículo deletado com sucesso!';
    }
}
// Deletar veículo (apenas admin)
if (isset($_POST['delete_veiculo']) && isset($_SESSION['admin']) && $_SESSION['admin']) {
    $delete_id = (int)$_POST['delete_veiculo'];
    $stmt = $conn->prepare("DELETE FROM veiculos WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    header('Location: veiculos.php?msg=2');
    exit();
}
// Cadastro de veículo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = strtoupper(trim($_POST['placa']));
    $modelo = trim($_POST['modelo']);
    if ($placa && $modelo) {
        $sql = "INSERT INTO veiculos (placa, modelo) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $placa, $modelo);
        if ($stmt->execute()) {
            header('Location: veiculos.php?msg=1');
            exit();
        } else {
            $msg = 'Erro: placa já cadastrada.';
        }
    }
}
// Consulta veículos
$veiculos = $conn->query("SELECT * FROM veiculos ORDER BY placa");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Veículos - São Francisco</title>
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
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
        <h2>Cadastro de Veículo</h2>
        <?php if ($msg) echo '<p>'.$msg.'</p>'; ?>
        <form method="post">
            <input name="placa" placeholder="Placa" maxlength="10" required>
            <input name="modelo" placeholder="Modelo" required>
            <button type="submit">Cadastrar</button>
        </form>
        <?php else: ?>
        <h2>Veículos Cadastrados</h2>
        <?php endif; ?>
        <h3>Veículos Cadastrados</h3>
        <table border="1" width="100%" style="margin-top:10px;">
            <tr><th>Placa</th><th>Modelo</th><?php if(isset($_SESSION['admin']) && $_SESSION['admin']) echo '<th>Ações</th>'; ?></tr>
            <?php while ($v = $veiculos->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($v['placa'])?></td>
                <td><?=htmlspecialchars($v['modelo'])?></td>
                <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja deletar este veículo?');">
                        <input type="hidden" name="delete_veiculo" value="<?=$v['id']?>">
                        <button type="submit" class="danger">Deletar</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
