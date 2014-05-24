<?php
$list = array('1.0.0', '1.0.1');

foreach ($list as $version)
{
    $base = __DIR__ . '/../package';
    $dir = "$base/skel";
    $fn = "$base/zips/ngyuki-composer-shared-installer-example-$version.zip";
    createZipArchive($version, $fn, $dir);
}

function createZipArchive($version, $fn, $dir)
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir,
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_SELF
        ),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $zip = new ZipArchive();
    $zip->open($fn, ZipArchive::CREATE);

    /* @var $it RecursiveDirectoryIterator */
    foreach ($iterator as $it)
    {
        if ($it->isDir())
        {
            $zip->addEmptyDir($it->getSubPathname());
        }
        else
        {
            if ($it->getSubPathname() === 'composer.json')
            {
                $file = file_get_contents($it->getPathname());
                $file = str_replace('%version%', $version, $file);
                $zip->addFromString($it->getSubPathname(), $file);
            }
            else
            {
                $zip->addFile($it->getPathname(), $it->getSubPathname());
            }
        }
    }

    $zip->close();
}
