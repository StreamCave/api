# API Streamcave

## Introduction
StreamCave est une application web permettant à quiconque de s'auto-gérer une production audiovisuelle à distance pour tout types de situation.

## Versions
- Symfony 6.1.*
- PHP 8.1
- MariaDB 10.10.2
- Docker 20.10.*
- Docker-compose 2.12.2
- Composer 2.2.7

## Installation
### Prérequis
- Symfony CLI
- Composer
- Open SSL
- PHP
- MySQL ou MariaDB (changer dans le .env)
- Postman (recommandé en local)
- Docker (optionnel)

### Installation
- Cloner le projet
- Installer les dépendances avec `composer install`
- Créer un .env avec les variables d'environnement par défaut de Symfony
- Installer OpenSSL sur son appareil
- Créer la base de données avec `php bin/console doctrine:database:create`
- Créer les tables avec `php bin/console doctrine:migrations:migrate`
- Lancer le serveur avec `symfony server:start` ou `symfony serve -d` pour le lancer en arrière-plan

## Utilisation
### Routes
Pour accéder à toutes les routes de l'api avec les requêtes possibles, veuillez vous rendre sur ```api/api_documentation```

### Important 
Vous devez être connecté pour avoir accès à l'entièreté de l'API hormis pour se créer un compte ou se connecter. Un token JWT vous sera demandé pour toutes les autres URL.

### Création d'un compte
- Se rendre sur ```api/register```
- Insérer en BODY :
```json
{
    "email": "admin@example.com",
    "password": "admin"
}
```
- Envoyer votre requête en *POST*

### Connexion
- Se rendre sur ```api/login```
- Insérer en BODY :
```json
{
    "username": "admin@example.com",
    "password": "admin"
}
```
- Envoyer votre requête en *POST*
- Vous recevrez un token JWT à utiliser pour toutes les autres requêtes

### Accès à l'API
- Se rendre sur ```api/...```
- Insérer le token précedemment généré dans le header de votre requête sous le nom *Authorization*
- Envoyer votre requête en *GET*, *POST*, *PUT* ou *DELETE*
- Vous recevrez une réponse en *JSON*

## Tests
### Prérequis
- PHPUnit

### Lancement des tests
- Lancer les tests avec `php bin/phpunit`
- Lancer les tests avec Docker avec `docker-compose run php bin/phpunit`
- Lancer les tests avec Docker en arrière-plan avec `docker-compose up -d php bin/phpunit`
