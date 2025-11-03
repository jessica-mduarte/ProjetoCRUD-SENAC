<?php

require __DIR__ . '/includes/db.php';

// Inicio - logica de busca

// $_GET pega o valor digitando no campo de busca (se existir)
// trim() remove espaços antes e depois do texto;

$busca = trim($_GET['busca'] ?? '');

// get é usado aqui para consultar dados , nao esta salvando nada.

// verifica se o usuario digitou algo 

if ($busca !== ''){
    // se tiver texto na busca , o sql filtra pelo nome ou email
    $sql = 'SELECT id, nome, email, telefone, foto, data_cadastro
            FROM cadastros
            WHERE nome LIKE :busca OR email LIKE :busca
            ORDER BY id DESC'; // ORDENA PELOS IDS DO MAIOR PRO MENOR (CADASTROS MAIS NOVOS PRIMEIRO)
//prepara o comando SQL 

$stmt = db()->prepare($sql);

// executa substituindo o placeholder :busca
// o % antes e depois permite buscar qualquer parte do nome/email
$stmt->execute([':busca' => "%$busca%"]);

} else {
    // se o campo de busca estiver vazio, lista tudo
    $sql = 'SELECT id, nome, email, telefone, foto, data_cadastro
    FROM cadastros
    ORDER BY id DESC';

$stmt = db()->prepare($sql);
$stmt->execute();    

}

// fetchAll() busca todos os resultados e retorna como array associativo
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fim logica de busca

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de cadastros</title>
</head>
<body>
    <h1>Lista de cadastros</h1>

    <form method="get">
        <input type="text" name="busca" placeholder="Pesquisar..." value="<?= htmlspecialchars($busca)?>">

        <button type="submit">Buscar</button>

        <a href="listar.php">Limpar</a>
    </form>
<p><a href="formulario.php">+ Novo cadastro</a></p>  

<?php if (!$registros): ?>
    <!-- Se não houver resultados --> 
    <p>Nenhum cadastro encontrado.</p>

<?php else: ?>
    
    <table border ="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Foto</th>
                <th>Data de Cadastro</th>
                <th>Ações</th>
            </tr>
        </thead>
    

    <tbody>
        <?php

        // foreach -> estrutura que percorre todos os registros do banco
        // $registros -> lista com todos os cadastros vindos do banco
        // $r -> representa UM registro por vez dentro do loop

        foreach ($registros as $r):
            ?>

        <tr>
            <td><?= (int)$r['id'] ?></td>
            <td><?= htmlspecialchars($r['nome']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['telefone']) ?></td>

            <td>
                <?php if (!empty($r['foto'])): ?>
                    <img src="<?= htmlspecialchars($r['foto']) ?>" alt="Foto" style="max-width:80px; max-height:80px;">
                    <?php else: ?>
                        -
                    <?php endif; ?>    
            </td>
             
            <!-- Exibe data, se existir -->
            <td> 
                <?= htmlspecialchars($r['data_cadastro'] ??'')?>
            </td>

            <!-- Links para editar ou excluir -->
             <td> 
                <a href="editar.php?id=<?= (int)$r['id'] ?>">Editar</a>
                <a href="deletar.php?id=<?= (int)$r['id'] ?>"
                onclick="return confirm('Tem certeza que deseja excluir esse registro?');">Excluir</a>
                    </td>

    

        </tr>   
        <?php endforeach; ?>
                    
    </tbody>
    </table>
    <!-- Fim da tabela de resultados -->

    <?php endif; ?>
</body>
</html>
