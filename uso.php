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
        $msg = 'Uso registrado!';
    } elseif ($_GET['msg'] == '2') {
        $msg = 'Todos os registros de uso foram apagados!';
    }
}
// Limpar todos os registros de uso (apenas admin)
if (isset($_POST['clear_usos']) && isset($_SESSION['admin']) && $_SESSION['admin']) {
    $conn->query("DELETE FROM uso_veiculos");
    registrar_log($_SESSION['usuario'], 'limpar_usos', 'Limpou todos os registros de uso de veículos');
    header('Location: uso.php?msg=2');
    exit();
}
// Registrar uso de veículo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $veiculo_id = $_POST['veiculo_id'];
    $motorista_id = $_POST['motorista_id'];
    $data_saida = $_POST['data_saida'];
    $data_retorno = isset($_POST['data_retorno']) ? $_POST['data_retorno'] : null;
    $observacao = trim($_POST['observacao']);
    $usuario_id = $_SESSION['usuario_id'];
    if ($veiculo_id && $motorista_id && $data_saida) {
        if ($data_retorno) {
            $sql = "INSERT INTO uso_veiculos (veiculo_id, motorista_id, usuario_id, data_saida, data_retorno, observacao) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiisss', $veiculo_id, $motorista_id, $usuario_id, $data_saida, $data_retorno, $observacao);
        } else {
            $sql = "INSERT INTO uso_veiculos (veiculo_id, motorista_id, usuario_id, data_saida, observacao) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiiss', $veiculo_id, $motorista_id, $usuario_id, $data_saida, $observacao);
        }
        if ($stmt->execute()) {
            $usuario_nome = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'desconhecido';
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 'desconhecido';
registrar_log($usuario_nome, 'registrar_uso', 'Usuário ID: ' . $usuario_id . ' registrou uso: Veículo ID ' . $veiculo_id . ', Motorista ID ' . $motorista_id . ', Saída: ' . $data_saida . ', Retorno: ' . $data_retorno);
            header('Location: uso.php?msg=1');
            exit();
        } else {
            $msg = 'Erro ao registrar uso.';
        }
    }
}
// Consulta para selects
$veiculos = $conn->query("SELECT * FROM veiculos ORDER BY placa");
$motoristas = $conn->query("SELECT * FROM motoristas ORDER BY nome");
// Filtro de datas para consulta de usos
$data_ini = isset($_GET['data_ini']) ? $_GET['data_ini'] : '';
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$where = '';
$params = [];
$types = '';
if ($data_ini && $data_fim) {
    $where = 'WHERE u.data_saida >= ? AND u.data_saida <= ?';
    $params[] = $data_ini . ' 00:00:00';
    $params[] = $data_fim . ' 23:59:59';
    $types = 'ss';
} elseif ($data_ini) {
    $where = 'WHERE u.data_saida >= ?';
    $params[] = $data_ini . ' 00:00:00';
    $types = 's';
} elseif ($data_fim) {
    $where = 'WHERE u.data_saida <= ?';
    $params[] = $data_fim . ' 23:59:59';
    $types = 's';
}
$sql_usos = "SELECT u.id, v.placa, m.nome as motorista, u.data_saida, u.data_retorno, u.observacao FROM uso_veiculos u JOIN veiculos v ON u.veiculo_id=v.id JOIN motoristas m ON u.motorista_id=m.id $where ORDER BY u.data_saida DESC";
if ($where) {
    $stmt = $conn->prepare($sql_usos);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $usos = $stmt->get_result();
} else {
    $usos = $conn->query($sql_usos . " LIMIT 20");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Uso de Veículos - São Francisco</title>
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
        <h2>Registrar Uso de Veículo</h2>
        <?php if ($msg) echo '<p>'.$msg.'</p>'; ?>
        <form method="post">
            <select name="veiculo_id" required>
                <option value="">Selecione o veículo</option>
                <?php foreach ($veiculos as $v): ?>
                <option value="<?=$v['id']?>">Placa: <?=htmlspecialchars($v['placa'])?> - <?=htmlspecialchars($v['modelo'])?></option>
                <?php endforeach; ?>
            </select><br>
            <select name="motorista_id" required>
                <option value="">Selecione o motorista</option>
                <?php foreach ($motoristas as $m): ?>
                <option value="<?=$m['id']?>">Nome: <?=htmlspecialchars($m['nome'])?> - CNH: <?=htmlspecialchars($m['cnh'])?></option>
                <?php endforeach; ?>
            </select><br>
            <div class="form-row">
                <input type="datetime-local" name="data_saida" required placeholder="Data/Hora de Saída">
                <input type="datetime-local" name="data_retorno" placeholder="Data/Hora de Retorno (opcional)">
            </div>
            <input type="text" name="observacao" placeholder="Observação"><br>
            <button type="submit">Registrar Uso</button>
        </form>
        <?php if (isset($_SESSION['admin']) && $_SESSION['admin']): ?>
        <form method="post" onsubmit="return confirm('Tem certeza que deseja apagar TODOS os registros de uso? Esta ação não pode ser desfeita!');" style="margin-bottom:20px;">
            <button type="submit" name="clear_usos" class="danger">Limpar Registros de Uso</button>
        </form>
        <?php endif; ?>
        <h3>Registros de Uso<?=($data_ini||$data_fim)?' (Filtrado)':'s Recentes'?></h3>
        <table border="1" width="100%" style="margin-top:10px;">
            <tr><th>Placa</th><th>Motorista</th><th>Saída</th><th>Retorno</th><th>Obs</th></tr>
            <?php while ($u = $usos->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($u['placa'])?></td>
                <td><?=htmlspecialchars($u['motorista'])?></td>
                <td><?=htmlspecialchars($u['data_saida'])?></td>
                <td><?=htmlspecialchars($u['data_retorno'])?></td>
                <td><?=htmlspecialchars($u['observacao'])?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <h3>Consulta de Usos de Veículo</h3>
        <form method="get" style="margin-bottom:15px;">
            <label>Data Inicial: <input type="date" name="data_ini" value="<?=htmlspecialchars($data_ini)?>"></label>
            <label>Data Final: <input type="date" name="data_fim" value="<?=htmlspecialchars($data_fim)?>"></label>
            <button type="submit">Filtrar</button>
            <?php if ($data_ini || $data_fim): ?>
                <a href="uso.php" style="margin-left:10px;">Limpar Filtro</a>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
