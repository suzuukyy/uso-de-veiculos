<?php
// Configuração do banco de dados
$host = 'localhost';
$db = 'uso_veiculos';
$user = 'root'; // Altere para o usuário do seu banco no KingHost
$pass = '';    // Altere para a senha do seu banco no KingHost

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Erro de conexão: ' . $conn->connect_error);
}
?>
