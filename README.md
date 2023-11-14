Symfony Home Budget App
========================

The "Symfony Home Budget App" is a budgeting app for easier expense tracking.

Requirements
------------
* PHP 8.1.0 or higher;
* MySQL mariadb;
* Symfony 6
* Docker
* and the [usual Symfony application requirements][2].

Installation
------------

**1.** clone the repository
$ git clone git@github.com:jmucak/home-budget-app.git
$ cd home-budget-app/
$ composer install

Usage
-----
**1.** Configure .env file
DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/app?serverVersion=8.0.32&charset=utf8mb4"

Create private and public .pem keys for jwt token. (config/jwt)

```bash
$ symfony console lexik:jwt:generate-keypair
```

# More information at <a href="https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html">https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html</a>

**2.** start docker container
```bash
$ docker compose up
```

**3.** start server
```bash
$ symfony server:start
```

Then access the application in your browser at the given URL (<http://127.0.0.1:8000/> by default).
API route is at "/api"