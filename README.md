# Filament Template
Filament Template is a starter kit for Laravel application with pre-installed Laravel shield and required production configurations.

## Installation
Clone this project
```
git clone https://github.com/blalmal10a/filament-template.git

git remote remove origin
``` 

Generate .env file
```
sh init.sh
composer install

php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
```
For windows users
```
./winit.bat
composer install

php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
```

Install dependencies
```
composer install

php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
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

USE THE BELOW COMMANDS IF ALL SETTINGS ARE CORERCT
```
composer install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate
php artisan db:seed
```

