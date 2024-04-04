# Desafio Supliu
## Requisitos
 * [PHP 8.2+](https://www.php.net/)
 * [Composer 2.5.5+](https://getcomposer.org/)
 * [Postgres 13+](https://www.postgresql.org/)

## Funcionalidades

* ### Endpoints e suas funções
O backend conta com um total de 17 Endpoints, onde 9 são destinados ao controle de álbuns, 7 ao de faixas e 1 é usado especialmente para popular o banco.

#### Controle de Álbuns
1. ```/api/albuns``` (GET): Permite listar todos os álbuns já cadastrados e seus atributos;
1. ```/api/albuns/simplify``` (GET): Permite listar todos os álbuns já cadastrados de forma simplificada;
1. ```/api/albuns/{id}``` (GET): Exibe os dados de um álbum especificado pelo ```{id}```;
1. ```/api/albuns``` (POST): Permite cadastrar um novo Álbum;
1. ```/api/albuns/{id}``` (PUT): Permite editar um álbum identificado pelo ```{id}```;
1. ```/api/albuns/{id}``` (DELETE): Remove o álbum especificado pelo ```{id}```;
1. ```/api/albuns/{id}/faixas```  (GET): Exibe as faixas de um álbum especificado pelo ```{id}```;
1. ```/api/albuns/{album_id}/faixa/{faixa_id}``` (POST): Atribui a faixa ao álbum, especificados pelos indicadores ```{album_id}``` e ```{faixa_id}```;
1. ```/api/albuns/{album_id}/faixa/{faixa_id}``` (DELETE): Desvinlcula a faixa do álbum, especificados pelos indicadores ```{album_id}``` e ```{faixa_id}```.

#### Controle de Faixas
1. ```/api/faixas``` (GET): Lista todas as faixas já cadastradas;
1. ```/api/faixas/simplify``` (GET): Permite listar todas as faixas já cadastradas de forma simplificada;
1. ```/api/faixas/{id}``` (GET): Exibe os dados de uma faixa especificada pelo ```{id}```;
1. ```/api/faixas``` (POST): Cadastra uma nova faixa especificada pelo ```{id}```;
1. ```/api/faixas/{id}``` (PUT): Permite editar uma faixa identificada pelo ```{id}```;
1. ```/api/faixas/{id}``` (DELETE): Remove a faixa especificada pelo ```{id}```;
1. ```/api/faixas/{id}/albuns``` (GET): Exibe os álbuns em que a faixa especificada pelo ```{id}``` está inclusa;

#### Outros
1. ```/api/carregarDoSpotify``` (GET): Esse endpoint faz uma consulta recursiva à API do spotify e cadastra automaticamente Álbuns e Faixas de determinado artista. O único atributo obrigatório é o ```token``` os demais são por padrão os necessários para o nosso caso.

    * ```token``` Deve conter seu token bearer da API do Spotify ( Obrigatório );
    * ```limit``` Total de Álbuns a serem cadastrados (Máx.: 50);
    * ```offset``` Posição no banco do spotify onde deseja iniciar a consulta;
    * ```artist``` Id do artista no Spotify;
    
Exemplo:
```
http://API_URL/api/carregarDoSpotify?token=SEU_TOKEN_SPOTIFY&limit=30
```
 

* ### Validação dos dados:
No backend a validação é feita pelo próprio Laravel tanto durante o cadastro quanto a atualização, retornando um erro ```422``` e descrevendo a inconformidade.

 ###### Álbum

1. ```nome```: Required|String|Unique|Max:100;
1. ```ano```: Required|Integer|Digits:4;
1. ```imagem```: String;
1. ```spotify_link```: String.

 ###### Faixa

1. ```nome```: Required|String|Unique|Max:100;
1. ```duracao```: Required|Integer; ( Salva em *ms*)
1. ```spotify_link```: String.
1. ```albuns```: Required|Array.

A relação entre Álbuns e Faixas é de m:m, entretanto para criar uma faixa deve obrigatoriamente colocá-la num Álbum já existente.

## Instalação

* ### Banco de dados
Instalar o Postgres 13 ou superior.

Criar o banco de dados no Postgres: 
```sql
CREATE DATABASE nome_do_banco
```

* ### PHP
 Após instalar o php 8.2 ou superior. No arquivo ```php.ini``` descomente as seguintes linhas:
```
extension=pdo_pgsql
extension=zip
```

* ### Composer
Baixar e instalar o composer ( Lembre-se de apontar para o path correto do php )

Rode ```composer install``` na pasta do projeto para Instalar todas as dependências 

## Configuração
* ### Tradução das mensagens de erro
Renomeio o arquivo ```.env.example``` para ```.env```

Na linha ```APP_LOCALE=en``` mude para ```APP_LOCALE=pt_BR``` 
* ### Configurar a conexão com o banco de dados
Ainda no arquivo ```.env```, no bloco ```DB_CONNECTION``` insira as informações corretas do seu banco de dados.

```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nome_do_banco_dedados
DB_USERNAME=nome_do_usuario_do_banco_de_dados
DB_PASSWORD=senha_do_banco_de_dados
```
* ### Inicializar o banco com ```artisan```
```
php artisan migrate
```

* ### Iniciar servidor
```
php artisan serve
```