# Laravel Test

## Setup

Run `cp .env.example .env`

Run `composer install`

Run `npm install` (optional as we aren't dealing with views or frontend here but as we are working on normal version of laravel it is required here e.g; no breeze or other package)

## Database

Create a database and update the `.env` file
Update following sections in `.env` file with desired DB details

```
DB_CONNECTION=mysql
DB_PORT=3306
DB_HOST=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

## Migration

Run `php artisan migrate` to add tables

## Design Schema

**Locales**: contain all languages with `code/name` e.g; en => English, fr => French with ids 1,2 respectively

**Translations**: contain `key/value` items with locale id e.g; 1: Login, Login, 1, 2:Payment, Paiement, 2

**Tags**: contain different items for `context` e.g; 1:mobile, 2:desktop

**Taggables**: contain relation between `translation and context` e.g; Login, desktop => 2

**Tokens**: contain the details regarding all user tokens

## Caching

File caching is used to get fast responses. Update `.env` file with the following

```
CACHE_STORE=file
```

**GET**: gets from cache or hits the DB and caches the data

**POST**: clears the cache for and caches again for next GET request

**PUT**: clears the cache for and caches again for next GET request

**DELETE**: clears the cache for and caches again for next GET request

## Testing Scalibility

Run `php artisan db:seed --class=TranslationSeeder`, defaults to 100000, 500
or
Run `php artisan seed:translations --total=100000 --chunk=500` command, defaults to 1000, 100

## Token Based Authentication

Using laravel's `Sanctum` is the easiest and viable option here with minimal configuration and setup. Default comes with null so need to add and update `.env` and `sanctum.php` in config respectively as below

```
SANCTUM_TOKEN_EXPIRATION=60
```

```php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),
```

## API Documentation

Run `php artisan scribe:generate` to generate the docs

- OpenAPI Spec: [`${APP_URL}/docs.openapi`](`${APP_URL}/docs.openapi`)
- Postman Collection: [`${APP_URL}/docs.postman`](`${APP_URL}/docs.postman`)
- Web UI: [`${APP_URL}/docs`](`${APP_URL}/docs`)

## Testing and Code Coverage

Run `XDEBUG_MODE=coverage php artisan test --coverage --coverage-html coverage/` for running all tests
and

Run `xdg-open coverage/index.html` Linux
or
`open coverage/index.html` for Mac
or
`start coverage\index.html` ofr Windows
