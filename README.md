## Get Started

### Requirements

- **PHP 8.1+**
- **Laravel 10+**
- **Apiato 10+**
- **PHPUnit 10+**

### Installation

To install the package via composer, Run:

```bash
composer require batyukovstudio/apiato-swagger-generator
```

### Usage
Generate documentation base:
```bash
php artisan swagger:generate
```
Run tests
```bash
php artisan test
```

### Tests integration
1. Setup PHPUnit with apiato: https://apiato.io/docs/components/optional-components/tests/
2. Include Batyukovstudio\ApiatoSwaggerGenerator\PhpUnitExtension extension (see phpunit.example.test)
```
    <extensions>
        <bootstrap class="Batyukovstudio\ApiatoSwaggerGenerator\PhpUnitExtension">
        </bootstrap>
    </extensions>
```
3. To enable recording test responses import trait to your parent TestCase
```php
use Batyukovstudio\ApiatoSwaggerGenerator\Traits;
```
4. Enjoy it 😇😇😇
