## Get Started

### Requirements

- **PHP 8.1+**
- **Laravel 10+**
- **Apiato 10+**

### Installation

To install the package via composer, Run:

```bash
composer require batyukovstudio/apiato-swagger-generator
```

### Usage

```bash
php artisan swagger:generate
```

### Tests integration

Конвенция Apiato по написанию тестов:
```https://apiato.io/docs/components/optional-components/tests```

#### Алгоритм внедрения:

Для интеграции тестирования необходимо по конвенции Apiato внедрить тест маршрута, общий вид:

App\Section\Container\Tests\Unit\UI\Routes\RouteNameTest

Данный тест должен реализовывать интерфейс TestRouteInterface из данного пакета. При соблюдении
данных условий команда php artisan swagger:generate самостоятельно возьмёт тестовые данные из
класса теста и выполнит запрос к маршруту с их помощью, соответственно будет сгенерирован
достоверный пример ответа на данный запрос

