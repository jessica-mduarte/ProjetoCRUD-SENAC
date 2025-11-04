<?php 

// Inicio da conexão com o banco e verificação do ID

require __DIR__ . '/includes/db.php';

// Verifica se veio o ID pela URL (GET)
$id = (int)($_GET['id'] ?? '');

// Se o ID for inválido (zero ou vazio), redireciona para a lista
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

// Fim da conexão com o banco e verificação do ID

// ==============================================================
// Busca do registro para excluir

$sql = 'SELECT * FROM cadastros WHERE id = :id';
$stmt = db()->prepare($sql);
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

// Caso não encontra nada, volta para a lista
if (!$registro) {
    header('Location: listar.php');
    exit;
}

// Fim da busca do registro
// ================================================================

// Início - Exclusão do registro

try {
    if (!empty($registro['foto']) && file_exists(__DIR__ . '/' . $registro['foto'])) {
        unlink(__DIR__ . '/' . $registro['foto']);
    }
    //  if (!empty($registro['foto'])
    // Verifica se o campo foto no banco não está vazio

    // file_exists(__DIR__ . '/' . $registro['foto'])
    // Confirma se o arquivo realmente existe na pasta do servidor antes de tentar apagar

    // unlink 
    // É a função nativa do PHP que deleta um arquivo físico

    // Comando SQL pra excluir o registro

    $sql = 'DELETE FROM cadastros WHERE id = :id';
    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $id]);

    // redireciona de volta pra lista após excluir
    header('Location: listar.php?msg=excluido');
    exit;
} //Chave do try
catch (PDOException $e) {
    // Caso de erro, mostra a seguinte mensagem:
    echo '<p style="color:red;">Erro ao excluir: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
// Fim da exclusão do registro
// =============================================================================================