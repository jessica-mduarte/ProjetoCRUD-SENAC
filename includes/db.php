<?php
function db(): PDO {



static $pdo;

if (!$pdo) {

    try {
        $dsn = 'mysql:host=127.0.0.1;dbname=jessica_db;charset=utf8mb4';
    
        $pdo = new PDO(
            $dsn,   //Caminho do banco
            'root', //Usuário do banco (No Xampp por padrão é "root")
            '',     //Senha do banco(no Xampp por padrão é vazia)
        
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            //Define que se der erro, o PDO lançará uma exceção (erro visível)

            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            
        ]
        );

        // echo "<b> Conectado com sucesso ao banco!</b>";
    
    } catch (PDOException $e) {

        echo "<b>Erro ao conectar ao banco: </b> ". $e->getMessage();

        exit;
    }
}
return $pdo;
}

if (basename(__FILE__) ===basename($_SERVER['SCRIPT_FILENAME'])) {
    db(); 
}