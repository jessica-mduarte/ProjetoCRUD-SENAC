<?php 

// Início: Conexão com o banco e verificação ID
// ============================================

require __DIR__ . '/includes/db.php';
// Verifica se o ID veio pela URL (GET)
$id = (int)($_GET['id'] ?? '');

// se o ID for invalido, redireciona para a lista 
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}
// FIM da conexão com o banco e verificação do ID

// Início - Busca do registro para excluir
// ========================================
$sql = 'SELECT * FROM cadastros WHERE id = :id';
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

// se não encontrar nada, voltar para lista
if (!$registro) {
    header ('Location: listar.php');
    exit;
}
// Fim da busca do registro
// ========================================

