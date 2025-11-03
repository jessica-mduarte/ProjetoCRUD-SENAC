### Criação de Banco de dados usando SQL (MySQL)
        CREATE DATABASE IF NOT EXISTS jessica_db -- cria o banco de dados caso ele não exista
            DEFAULT CHARACTER SET utf8mb4 		 -- definir o charset moderno (aceita emojis e acentos)
            COLLATE utf8mb4_general_ci;          -- define collation (cria regras de ordenação)

## Exclusão do Banco de dados 
        DROP DATABASE IF EXISTS jessica_db 


### Fechar ou não fechar php?

Nos arquivos PHP não é obrigatório fechar com ?>. <br>
Se o arquivo for só PHP, **é melhor deixar aberto**, evita erros por espaço em branco no final. Porém, **se tiver HTML junto**, se torna necessário fechar. 

Não é necessário fechar o PHP:

        <?php
        echo "Conexão realizada com sucesso!";

Uma página HTML onde devemos fechar PHP:

## PDO 

O PDO (PHP Data Objects) habilita uma forma moderna e segura de conectar o PHP ao banco de dados, enviando e recebendo dados do banco de um jeito **seguro** e **padronizado**. 

