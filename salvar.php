<?php
//carrega a função db() para conectar ao MySQL
require __DIR__ . '/includes/db.php';

//guardará msgs de erro
$erro='';

//indica se salvou com sucesso
$ok=false;

//Só processa se o método da requisição for POST (veio do formulário)
if ($_SERVER['REQUEST_METHOD'] ==='POST') {

    //1) Captura e limpar os dados enviados
    //Pega os valores do formulário via método POST
    //usa trim() para remover espaços extrar no começo e no fim

    $nome       =trim($_POST['nome']    ?? '');
    $email      =trim($_POST['email']   ?? '');
    $telefone   =trim($_POST['telefone']?? '');
    $foto       =trim($_POST['foto']    ?? '');

//2) Validações simples (evita dados incorretos antes de gravar no banco)
//Verifica se o nome foi preenchido e tem pelo menos 3 caracteres
// mb_strlen() função nativa php que conta caracteres, incluindo acentos(ex: "José" = 4)


//mb - sigla que impede contar acentos como um caractere extra

if ($nome == '' || mb_strlen($nome) < 3) {
    $erro = "Nome é obrigatório(mín. 3 caracteres).";

    //Verifica se o email está em formato valido (ex: nome@dominio.com)
    //filter_var() é uma função nativa php usada para filtrar/validar valores
    // FILTER_VALIDATE_EMAIL é uma constante nativa PHP que valida o formato do e-mail
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = 'E-mail inválido.';


    //Verifica se o telefone preenchido e tem pelo menos 8 dígitos
    //preg_replace() é uma função nativa PHP que substitui partes de texto usando expressões regulares
    //Aqui ela removerá tudo que não for um número (/D+ = "qualquer caractere não numérico")
    //depois usamos mb_strlen() pra contar qts dígitos sobraram
}elseif ($telefone === '' || mb_strlen(preg_replace('/\D+/', '', $telefone)) < 8)  {
    $erro = 'Telefone inválido.';
}


// Upload da foto: apenas executará se não houver erro anterior para evitar uma execução extra do servidor
 $foto = null; //valor padrão: começa vazia

//  Caso não exista erro de validação:
if ($erro === '' && isset ($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {

    // Verifica se houve erro no upload
    if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
        $erro = 'Erro ao enviar imagem.';
    } else {
        // Opcional: Limite de tamanho até 2MB
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $erro = 'Imagem muito grande (máx. 2MB)';
        }
           // Validar o tipo da imagem
    if ($erro == '') {
        // finfo - classe nativa do php usada para descobrir o tipo do arquivo
        // (MIME=formato do arquivo)
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        // $_FILES['foto']['tmp_name'] é o caminho temporário onde o PHP guarda o arquivo antes de mover para pasta final (como um "rascunho")
        $mime = $finfo->file($_FILES['foto']['tmp_name']);

        // Lista de tipos de imagem que o sistema aceita (extensão associada)
        $permitidos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
        ];

        // Faz a verificação se o formato está dentro dos formatos permitidos:
        // Caso não esteja, mostra msg de erro ao usuário
        if (!isset($permitidos[$mime])){
            $erro = 'Formato de imagem inválido. Formatos aceitos: JPG, PNG ou GIF.';
        }
    }
// Criamos a pasta de uploads caso ela ainda não exista
        if ($erro === ''){
            $dirUpload = __DIR__ . '/uploads'; //__DIR__ mostra a pasta atual do arquivo

        if (!is_dir($dirUpload)) {
            // is_dir verifica se a pasta existe.
            //mkdir() cria pastas.
            //0755 permissão padrão (admin pode tudo)
            // true = cria subpastas quando for necessário
            mkdir($dirUpload, 0755, true);
        }
        }
// Gera um nome único e adiciona a extensao correta
//uniqid() cria um nome random 
        $novoNome = uniqid('img_', true) . '/' . $permitidos[$mime];

        //Caminho completo de onde o arquivo será salvo
        $destino = $dirUpload . '/' . $novoNome;

        //move_uploadede_file() funçao nativa do php que move o arquivodo local temp (tmp_name) para o destino final (upload)
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {

            // guardaa apenas o caminho relativo para salvar no banco

            $foto = 'uploads/' . $novoNome;
        } else {
            $erro = "Falha ao salvar imagem no servidor.";
        }
    }

}

//3) Se não houve erro, tentamos salvar os dados no banco
if ($erro === '') {

    try {
        //SQL com placeholders nomeados (evita sql injection): uma camada extra de segurança com a conversão de strings
        //Os (:) indicam variáveis que serão substituídas depois
        $sql = 'INSERT INTO cadastros (nome, email, telefone)
            VALUES (:nome, :email, :telefone)';

//db() função personalizada que retorna a conexão PDO com o banco
//prepare() método nativo PDO que "pré-compila" o SQL no servidor
//isso aumenta a segurança e desempenho, pois separa o comando SQL dos dados

$query = db()->prepare($sql);

//execute() método nativo PDO que executa o comando preparado
//aqui passamos os valores que vao substituir os placeholders nomeados
$query->execute([
    ':nome'        => $nome,
    ':email'       => $email,
    ':telefone'    => $telefone,
    ':foto'        => $foto,
]);

$ok = true; //marca que o cadastro foi salvo com sucesso.
    
//catch (PDOException $e) captura erros lançados pelo PDO (função nativa do PHP para exceções)

} catch (PDOException $e) {

    //O código 23000 indica erro de violação da restrição(ex: email duplicado na coluna UNIQUE)
    if($e->getCode() === '23000') {
        //msg pro user
        $erro = 'Este e-mail já está cadastrado.';

        //Qlqr outro erro mostra a msg técnica(útil para depuração)
    }else {
        $erro = 'Erro ao salvar: ' . $e->getMessage();
        //getmessage() metodo nativo da classe Exception que devolve o texto do erro

    }
 
}
}
}
//Fechamos o <?php pois colocaremos HTML no arquivo para enviar as mensagens.

?>

<!doctype html>
<meta charset="utf-8">
<title>Salvar</title>
 
<!-- Se deu tudo certo no cadastro, mostra mensagem de sucesso -->
<?php if ($ok): ?>
  <p>Dados salvos com sucesso!</p>
  <p><a href="form.php">Voltar</a></p>
 
<!-- Se não deu certo, entra aqui -->
<?php else: ?>
 
  <!-- Se existe mensagem de erro, exibe em vermelho -->
  <?php if ($erro): ?>
    <!-- htmlspecialchars() → função nativa do PHP que converte caracteres especiais em HTML seguro -->
    <!-- Evita que alguém insira tags HTML ou scripts maliciosos dentro da mensagem -->
    <p style="color:red;"><?= htmlspecialchars($erro) ?></p>
 
  <!-- Se chegou aqui sem erro e sem POST, o usuário acessou a página diretamente -->
  <?php else: ?>
    <p>Nada enviado.</p>
  <?php endif; ?>
 
  <!-- Link pra voltar pro formulário -->
  <p><a href="form.php">Voltar</a></p>
 
<?php endif; ?>

