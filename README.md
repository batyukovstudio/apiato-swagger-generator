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
```xml
<extensions>
    <bootstrap class="Batyukovstudio\ApiatoSwaggerGenerator\PhpUnitExtension">
    </bootstrap>
</extensions>
```
3. Register global middleware in your main Kernel class (HttpKernel in Apiato)
```php
use Batyukovstudio\ApiatoSwaggerGenerator\Middlewares\SwaggerGeneratorMiddleware;

class HttpKernel extends LaravelHttpKernel
{
    protected $middleware = [
        // Laravel middlewares
        SwaggerGeneratorMiddleware::class,
        // other middlewares
    ];
}
```
4. Import trait to your parent TestCase to enable recording test responses
```php
use Batyukovstudio\ApiatoSwaggerGenerator\Traits\CanRecordTestResponses;

class YourParentTestCase extends AbstractTestCase
{
    use CanRecordTestResponses;
}
```

5. Add hasAdminRoles: bool to User model
```php
public function hasAdminRole(): bool
{
    return $this->hasRole(config('appSection-authorization.admin_role'));
}
```

6. Enjoy it 😇😇😇
