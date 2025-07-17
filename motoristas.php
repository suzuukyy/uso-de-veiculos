<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit();
}
include 'config.php';
include_once 'log.php';
$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == '1') {
        $msg = 'Motorista cadastrado!';
    } elseif ($_GET['msg'] == '2') {
        $msg = 'Motorista deletado com sucesso!';
    }
}
// Deletar motorista (apenas admin)
if (isset($_POST['delete_motorista']) && isset($_SESSION['admin']) && $_SESSION['admin']) {
    $delete_id = (int)$_POST['delete_motorista'];
    $stmt = $conn->prepare("DELETE FROM motoristas WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    registrar_log($_SESSION['usuario'], 'delete_motorista', 'Deletou motorista ID: ' . $delete_id);
    header('Location: motoristas.php?msg=2');
    exit();
}
// Cadastro de motorista
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cnh = trim($_POST['cnh']);
    if ($nome && $cnh) {
        $sql = "INSERT INTO motoristas (nome, cnh) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $nome, $cnh);
        if ($stmt->execute()) {
            registrar_log($_SESSION['usuario'], 'cadastro_motorista', 'Motorista cadastrado: ' . $nome . ' | CNH: ' . $cnh);
            $msg = 'Motorista cadastrado!';
        } else {
            $msg = 'Erro: CNH já cadastrada.';
        }
    }
}
// Consulta motoristas
$motoristas = $conn->query("SELECT * FROM motoristas ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Motoristas - São Francisco</title>
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
        <h2>Cadastro de Motorista</h2>
        <?php if ($msg) echo '<p>'.$msg.'</p>'; ?>
        <form method="post">
            <div class="form-row">
                <input name="nome" placeholder="Nome" required>
                <input name="cnh" placeholder="CNH" required>
            </div>
            <button type="submit">Cadastrar</button>
        </form>
        <?php else: ?>
        <h2>Motoristas Cadastrados</h2>
        <?php endif; ?>
        <h3>Motoristas Cadastrados</h3>
        <table border="1" width="100%" style="margin-top:10px;">
            <tr><th>Nome</th><th>CNH</th><?php if(isset($_SESSION['admin']) && $_SESSION['admin']) echo '<th>Ações</th>'; ?></tr>
            <?php while ($m = $motoristas->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($m['nome'])?></td>
                <td><?=htmlspecialchars($m['cnh'])?></td>
                <?php if(isset($_SESSION['admin']) && $_SESSION['admin']): ?>
                <td>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja deletar este motorista?');">
                        <input type="hidden" name="delete_motorista" value="<?=$m['id']?>">
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
