
## Example

Edit `composer.json`.

```json
{
    "require": {
        "symfony/filesystem": "~2.0"
    },
    "require-dev": {
        "phpunit/phpunit": "3.7.*",
        "ngyuki/composer-shared-installer": "*"
    },
    "extra": {
        "shared": {
            "exclude": [
                "phpunit/phpunit"
            ]
        }
    }
}
```

Run `composer update` command.

```
$ composer update
```

Create `sample.php`.

```php
<?php
require 'vendor/autoload.php';

use Symfony\Component\Filesystem\Filesystem;

$ref = new ReflectionClass('Symfony\Component\Filesystem\Filesystem');
echo $ref->getFileName(), PHP_EOL;

$ref = new ReflectionClass('PHPUnit_Framework_TestCase');
echo $ref->getFileName(), PHP_EOL;
```

Run `sample.php`.

```
$ php sample.php
```

Symfony Filesystem will be installed global.

```
/home/your/.composer/shared/symfony/filesystem-2.3.6.0/Symfony/Component/Filesystem/Filesystem.php
```

PHPUnit will be installed local, because your exclude it.

```
/home/your/project/vendor/phpunit/phpunit/PHPUnit/Framework/TestCase.php
```
