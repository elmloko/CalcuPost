# POSTAL RATE CALCULATOR SYSTEM FOR THE BOLIVIAN POSTAL AGENCY ""

The postal calculator system is designed to facilitate the calculation of shipping rates for the bolivian postal agency, using as a basis the current tariff established in administrative resolution no. 03 of march 4, 2021. This tariff regulates the costs of the services. Postal services at a national and international level, ensuring that users can obtain accurate and up-to-date information on the costs associated with sending letters, packages and documents.

This system allows users to enter basic information, such as the type of shipment, weight, destination and required service (ordinary or priority), to obtain a detailed calculation in accordance with official rates. In addition, it is presented as an accessible and reliable tool to optimize shipping management, both for individual and corporate clients.

The main objective is to automate and simplify the calculation process, ensuring the correct application of current rates and minimizing manual errors in determining costs. This system is aligned with the commitment of the bolivian postal agency to provide an efficient and transparent service to all its users.

## LANGUAGES, FRAMEWORKS USED AND TOOLS 🛠️

* PHP 8.1.10
* LARAVEL 10
* LIVEWIRE
* COMPOSER
* MYSQL
  https://github.com/jeroennoten/Laravel-AdminLTE               	jeroennoten/laravel-adminlte
  https://spatie.be/docs/laravel-permission/v5/introduction    	spatie/laravel-permission
  https://laravel-livewire.com/                             				livewire/livewire
  https://github.com/biscolab/laravel-recaptcha				biscolab/laravel-recaptcha
  https://jwt-auth.readthedocs.io/en/develop/					tymon/jwt-auth
  https://log-viewer.opcodes.io/							opcodesio/log-viewer

## Installation

We clone the repository

```bash
git clone https://github.com/elmloko/CalcuPost.git
```

We install our dependencies with [XAMMP](https://www.apachefriends.org/es/download.html)

## DataBases

* We import the database to MySQL

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=calcupost
DB_USERNAME=root
DB_PASSWORD=
```

* migrate and seed in database

## Credentials

| User  | Pass  |
| ----- | ----- |
| Admin | admin |

## Authors and acknowledgment

Developers of this software

* Marco Antonio Espinoza Rojas

## System installation

* Install Node dependencies:

```bash
npm install
```

* Install Composer dependencies:

```bash
composer install
```

* Copy the environment configuration file:

```bash
cp .env.example .env
```

* Generate the application key:

```bash
php artisan key:generate
```

## System configuration

* System cleanup and optimization:

```bash
php artisan optimize
```

* Generate jwt token:

```bash
php artisan jwt:secret
```

* Capturing Entries:

```bash
php artisan pulse:check
```

## License

[GNU](https://www.gnu.org/licenses/gpl-3.0.en.html)
