<?php
function registrar_log($usuario, $acao, $detalhes = "") {
    $data = date("Y-m-d H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'IP desconhecido';
    $linha = "$data | Usuário: $usuario | IP: $ip | Ação: $acao | Detalhes: $detalhes\n";
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }
    file_put_contents($logDir . '/atividade.log', $linha, FILE_APPEND);
}
?>
