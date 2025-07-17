<?php
session_start();
if (!isset($_SESSION['usuario']) || !$_SESSION['admin']) {
    header('Location: dashboard.php');
    exit();
}
include 'config.php';
include_once 'log.php';
require_once 'utils.php';
$msg = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == '1') {
        $msg = 'Usuário cadastrado!';
    } elseif ($_GET['msg'] == '2') {
        $msg = 'Usuário deletado com sucesso!';
    }
}
// Deletar usuário (apenas admin, não pode deletar a si mesmo)
if (isset($_POST['delete_user']) && isset($_SESSION['admin']) && $_SESSION['admin']) {
    $delete_id = (int)$_POST['delete_user'];
    if ($delete_id !== (int)$_SESSION['usuario_id']) {
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        $stmt->execute();
        registrar_log($_SESSION['usuario'], 'delete_usuario', 'Usuário ID deletado: ' . $delete_id);
        header('Location: usuarios.php?msg=2');
        exit();
    }
}
// Cadastro de usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_user'])) {

    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    $admin = isset($_POST['admin']) ? 1 : 0;
    if ($nome && $usuario && $senha) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, usuario, senha, admin) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssi', $nome, $usuario, $senha_hash, $admin);
        if ($stmt->execute()) {
            registrar_log($_SESSION['usuario'], 'cadastro_usuario', 'Usuário cadastrado: ' . $usuario);
            header('Location: usuarios.php?msg=1');
            exit();
        } else {
            $msg = 'Erro: usuário já existe.';
        }
    }
}
// Consulta usuários
$usuarios = $conn->query("SELECT id, nome, usuario, admin FROM usuarios ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Usuários - São Francisco</title>
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
        <h2>Cadastro de Usuário</h2>
        <?php if ($msg) echo '<p>'.$msg.'</p>'; ?>
        <form method="post">
            <input name="nome" placeholder="Nome" required>
            <input name="usuario" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <label><input type="checkbox" name="admin"> Administrador</label><br>
            <button type="submit">Cadastrar</button>
        </form>
        <h3>Usuários Cadastrados</h3>
        <table border="1" width="100%" style="margin-top:10px;">
            <tr><th>Nome</th><th>Usuário</th><th>Admin</th><th>Ações</th></tr>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
            <tr>
                <td><?=htmlspecialchars($u['nome'])?></td>
                <td><?=htmlspecialchars($u['usuario'])?></td>
                <td><?php
                    if ($u['nome'] === 'Administrador' || $u['usuario'] === 'admin') {
                        echo 'DEV';
                    } else {
                        echo ($u['admin'] ? 'Sim' : 'Não');
                    }
                ?></td>
                <td>
                    <?php
                    // Botão redefinir senha: só para o próprio usuário OU para admin/DEV
                    $pode_redefinir = (
                        $u['id'] === (int)$_SESSION['usuario_id'] ||
                        (isset($_SESSION['admin']) && $_SESSION['admin'])
                    ) && $u['nome'] !== 'Administrador' && $u['usuario'] !== 'admin';
                    if ($pode_redefinir): ?>
                        <a href="redefinir_senha.php?id=<?=$u['id']?>" class="btn">Redefinir Senha</a>
                    <?php endif; ?>
                    <?php if (
                        $u['id'] !== (int)$_SESSION['usuario_id'] &&
                        $u['nome'] !== 'Administrador' &&
                        $u['usuario'] !== 'admin'
                    ): ?>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja deletar este usuário?');">
                        <input type="hidden" name="delete_user" value="<?=$u['id']?>">
                        <button type="submit" class="danger">Deletar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
