<?php
namespace ngyuki\ComposerSharedInstaller\Example;

class Sample
{
    public static function main()
    {
        $version = self::getVersion() ?: "unknown";
        echo "ok $version ... " . __CLASS__ . PHP_EOL;
        return 0;
    }

    public static function getVersion()
    {
        static $version = false;

        if ($version === false)
        {
            $version = null;
            $file = file_get_contents(__DIR__ . '/../composer.json');
            $data = json_decode($file, true);

            if (isset($data['version']))
            {
                $version = (string)$data['version'];
            }
        }

        return $version;
    }
}
