# Digital Wallet

Digital Wallet is a REST API for managing deposits and withdrawals

# Getting Started

* Development Requirements
* Installation
* Starting Devevelopment Server
* Documentation
* Testing

## Development Requirements

This application currently runs on <b>Laravel 11 </b> and the development requirements to get this application up and
running are as follow:

* PHP 8.2+
* MySQL
* git
* Composer

## Installation

#### Step 1: Clone the repository

```bash
git clone https://github.com/ayodeleoniosun/digital-wallet.git
```

#### Step 2: Switch to the repo folder

```bash
cd digital-wallet
```

#### Step 3: Install all composer dependencies

```bash
composer install
```

#### Step 5: Setup environment variable

- Copy `.env.example` to `.env` i.e `cp .env.example .env`
- Update all the variables as needed

#### Step 6: Generate a new application key

```bash
php artisan key:generate
``` 

#### Step 7: Run database migration alongside the seeders

```bash
php artisan migrate:fresh --seed
``` 

Ensure that your mysql server is up before running the above command

## Starting Development Server

Run ```php artisan serve``` to start the server

### Documentation

The Postman API collection is locally available [Here](/public/postman_collection.json). <br/>

The Postman API collection is remotely available [Here](https://documenter.getpostman.com/view/18037473/2sA3QqfsXM)
. <br/>

### Testing

```bash
php artisan test --parallel
```
