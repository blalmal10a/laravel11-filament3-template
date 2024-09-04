# Filament Template
Filament Template is a starter kit for Laravel application with pre-installed Laravel shield and required production configurations.

## Installation
Clone this project
```
git clone https://github.com/blalmal10a/filament-template.git

git remote remove origin

composer install

cp .env.example .env
php artisan key:generate
php artisan storage:link
``` 

If your database credentials are different from the default, update the database credentials in the .env file.

```
php artisan migrate --seed
```

```
git remote add origin git@github.com:YOUR_GITHUB_USERNAME/YOUR_GITHUB_REPOSITORY
```
## Documentation

For full documentation, visit the [Filament documentation site](https://filamentphp.com/docs/).

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [B. Lalmalsawma](https://github.com/blalmal10a)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
