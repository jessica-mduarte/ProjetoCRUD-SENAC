<?php
// Início - Conexão e Captura do ID
// =======================================================================================
require __DIR__ . '/includes/db.php';

// Captura o ID que veio pela URL (ex: editar.php?id=3)
// Caso não exista/seja inválido, (0, texto, etc) volta para página de listagem

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}
// Fim da conexão e captura do ID
// =======================================================================================


// Início da busca do registro (para preencher o formulário)

$sql = 'SELECT id, nome, email, telefone, data_cadastro
        FROM cadastros
        WHERE id = :id';

$stmt = db()->prepare($sql);
$stmt->execute([':id' => $id]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

// Caso não encontre nenhum registro, volta para a lista

if (!$registro) {
    header('Location: listar.php');
    exit;
}

// Guarda a foto atual do registro (vinda do banco)
// Caso não seja enviada uma nova foto no formulário, essa continua
// sendo usada (para não apagar a existente)

$fotoAtual = $registro['foto'] ?? null;

// Fim da busca do registro
// ============================================================================================

// Início - Processamento POST (novos dados enviados ao "salvar")

$erro = '';
$ok   = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Captura dos dados
    $nome       =trim($_POST['nome']      ?? '');
    $email      =trim($_POST['email']     ?? '');
    $telefone   =trim($_POST['telefone']  ?? '');
    $fotoAtual  =$_POST['foto_atual']     ?? null;


// 2)) Validações básicas (igual usamos em salvar.php)

if ($nome === '' || mb_strlen($nome) < 3) {
    $erro = 'Nome é obrigatório (mín. 3 caracteres).';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido' ;
    }
    elseif ($telefone === '' || mb_strlen(preg_replace('/\D+/', '', $telefone)) <8) {
        $erro = 'Telefone inválido.';    
    }

    // Upload da nova foto (se enviada)
    $novaFoto = null; //se não enviar, mantemos a foto atual
if ($erro === '' && isset($_FILES['foto']['error']) !== UPLOAD_ERR_NO_FILE) {
    if($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
$erro = 'Erro ao enviar a imagem.';
    }
    else {
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $erro = 'Imagem muito grande (máx 2MB).';
        }

        // Valida tipo real arquivo (MIME)
        if ($erro === '') {
            $finfo = new finfo(FILEINFO_MIME_TYPE); // classe nativa para detectar MIME
            $mime  = $finfo->file($_FILES['foto']['tmp_name']); //tipo real do arquivo
            $permitidos = [
                'image/jpeg'    => 'jpg',
                'image/png'     => 'png',
                'image/gif'     => 'gif'
            ];
            if (!isset($permitidos[$mime])) {
                $erro = 'Formato de imagem inválido. Use JPG, PNG ou GIF.';
            }
        }
    
            //Garante existencia da pasta e move o arquivo
            if ($erro === '') {
                $dirUpload = __DIR__ . '/uploads';
                if (!is_dir($dirUpload)) {
                    mkdir($dirUpload, 0755, true);
                }

                $novoNome = uniqid('img_', true) . '.' . $permitidos[$mime]; //nome único
                $destino = $dirUpload . '/' . $novoNome;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $novaFoto = 'uploads/' . $novoNome; //salva no caminho relativo
                } else {
                    $erro = 'Falha ao salvar a imagem no servidor.';
                }
            }        

    }
}

// Caso tudo esteja OK, faz o UPDATE

    if ($erro === '') {
        try{
            // define qual foto sera salva: nova(se enviada) ou mantém a atual
            $fotoParaSalvar = $novaFoto !== null ? $novaFoto : $fotoAtual;

            $sql = 'UPDATE cadastros
            SET nome = :nome,
                email = :email,
                telefone = :telefone,
                foto = :foto
            WHERE id = :id';

            $stmt = db()->prepare($sql);
            $stmt->execute([
                ':nome'     => $nome,
                ':email'     => $email,
                ':telefone'  => $telefone,
                ':foto'     => $fotoParaSalvar,
                ':id'       => $id,
            ]);
        

        // Se trocou a foto, apaga a antiga do disco (se ela existir)
        if ($novaFoto !== null && !empty($fotoAtual) && file_exists(__DIR__ . '/' . $fotoAtual)) {
            unlink(__DIR__ . '/' . $fotoAtual);
        }

        $ok = true;

        // Redireciona para lista após atualizar (fluxo que voce quer)
        header('Location: listar.php?msg=atualizado');
        exit;
        } //chave do try fechada no final

        catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $erro = 'Este e-mail já está cadastrado.';
            }
            else {
                $erro = 'Erro ao atualizar: ' . $e->getMessage();
            }
        }
    } // Chave do if antes do try fechada aqui
} // chave do primeiro if fechada aqui

// =========================================================================================================================================================
// Fim - Processamento do POST

?>


<?php
    include __DIR__ . '/includes/header.php';
?>
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script> 
    <meta charset="utf-8" name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Editar Cadastro</title>
</head>
<body>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="text-center">
            
                <div class="card shadow">
                    <div class="card-body">
        <h3>Editar Cadastro</h3>
</div>
        <?php if ($erro): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <!-- Início do formulário de edição (pré-preenchido) -->
        <form method="post" enctype="multipart/form-data">
            <div class="text-center">
            <p>
                <label>Nome:<br>
                <input type="text" name="nome" required minlength="3"
                value="<?= htmlspecialchars($registro['nome'] ?? '') ?>">
                </label>
            </p>

            <p>
                <label>E-mail:<br>
                <input type="email" name="email" required
                value="<?= htmlspecialchars($registro['email'] ?? '') ?>">
                </label>
            </p>

            <p>
                <label>Telefone:<br>
                <input type="text" name="telefone" required 
                placeholder="(11) 91234-5678"
                value="<?= htmlspecialchars($registro['telefone'] ?? '') ?>">
                </label>
            </p>
            
            <p>
                Foto atual:
                <?php if (!empty($fotoAtual)): ?>
                    <br>
                    <img src="<?= htmlspecialchars($fotoAtual) ?>"
                    alt="Foto atual" style="max-width:120px; max-height:120px;">
                <?php else: ?>
                    (sem foto)
                <?php endif; ?>
            </p>
            
            <p> 
                <label>Trocar foto (opcional):<br>
                <input type="file" name="foto">
                </label>
            </p>

            <!-- Mantém o caminho da foto atual escondido(caso não troque) -->
            <input type="hidden" name="foto_atual" value="<?= htmlspecialchars($fotoAtual ?? '') ?>">
            
            <p>
                <button type="submit" class="btn btn-primary">Salvar alterações</button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </p>
                </div>
        </form>
        <!-- Fim do formulário de edição -->
 </div>
</div>
</div>
</div>
</body>
</html>

<?php
    include __DIR__ . '/includes/footer.php';
?>
