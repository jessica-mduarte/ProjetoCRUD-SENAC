<!-- Chamando o arquivo de cabeçalho no começo da página -->
<?php
    include __DIR__ . '/includes/header.php';
?>


<!DOCTYPE html>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script> 
<html lang='en'>

    
    <head>  
    <meta charset = 'utf-8'>
    <meta name = 'viewport' content = 'width=device-width, initial-scale=1.0'>
    <title>Cadastro</title>
</head>

<body>
    <h1>Cadastro</h1>

    
<form action = 'salvar.php' method ='post' entype="multipart/form-data">
<!-- O enctype servirá para sinalizar para o formulário que ele não aceitará apenas texto, mas outros formatos como arquivos. -->
    <p>
        <label>Nome:<br>
        <input type='text' name='nome' required>
        
    </label>

        </p>  
           <p> <label class='form-label' >E-mail:<br>
            <input type= 'email' name='email' id='formGroupExampleInput' required></p>
            
        
        
           <p><label>Telefone:</label><br>
            <input type='tel' name='telefone' required></label></p>
        
        <!-- Inserindo campo de foto -->
        <label>Foto</label>
        <input type= "file" name= "foto">

    <br>
    <br>
        <button type ='submit'>Enviar</button>

    </form>
</body>
</html>

<!-- Chamando o rodapé ao final da página -->
<?php
    include __DIR__ . '/includes/footer.php';
?>
