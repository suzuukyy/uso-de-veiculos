<?php
function log_acao($conn, $usuario, $acao, $detalhes = null) {
    $stmt = $conn->prepare('INSERT INTO logs (datahora, usuario, acao, detalhes) VALUES (NOW(), ?, ?, ?)');
    $stmt->bind_param('sss', $usuario, $acao, $detalhes);
    $stmt->execute();
}
