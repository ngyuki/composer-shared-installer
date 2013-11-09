<?php
namespace ngyuki\ComposerSharedInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // remove library installer
        $manager = $composer->getInstallationManager();
        $libraryInstaller = $manager->getInstaller('library');
        $manager->removeInstaller($libraryInstaller);

        // add shared installer
        $installer = new Installer($io, $composer);
        $manager->addInstaller($installer);
    }
}
