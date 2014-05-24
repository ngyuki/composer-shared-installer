<?php
$loader = null;
foreach (array(__DIR__ . '/../../../autoload.php', __DIR__ . '/../vendor/autoload.php') as $fn)
{
    if (file_exists($fn))
    {
        /** @noinspection PhpIncludeInspection */
        $loader = require $fn;
    }
}

if ($loader === null)
{
    echo 'You must set up the project dependencies, run "php composer.phar install"' . PHP_EOL;
    exit(1);
}

use ngyuki\ComposerSharedInstaller\Example\Sample;

exit(Sample::main());
