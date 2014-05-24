<?php
namespace ngyuki\ComposerSharedInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Util\Filesystem;

class Installer extends LibraryInstaller
{
    protected $includePatterns = null;
    protected $excludePatterns = null;

    /**
     * {@inheritDoc}
     */
    public function isInstalled(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (parent::isInstalled($repo, $package) == false)
        {
            return false;
        }

        return $repo->hasPackage($package) && $this->isInstalledBinaries($package);
    }

    /**
     * @param PackageInterface $package
     * @return bool
     */
    protected function isInstalledBinaries(PackageInterface $package)
    {
        $binaries = $package->getBinaries();

        foreach ($binaries as $bin)
        {
            $link = $this->binDir . DIRECTORY_SEPARATOR . basename($bin);

            if (!file_exists($link))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getPackageBasePath(PackageInterface $package)
    {
        if (!$this->isSharedInstallEnabled($package))
        {
            return parent::getPackageBasePath($package);
        }

        return $this->getSharedDir() . DIRECTORY_SEPARATOR . $package->getUniqueName();
    }

    /**
     * @param PackageInterface $package
     * @return bool
     */
    protected function isSharedInstallEnabled(PackageInterface $package)
    {
        // @todo disabled when prefer source
        if ($package->isDev())
        {
            return false;
        }

        $this->initializePattern();

        $name = $package->getName();

        foreach ($this->excludePatterns as $pattern)
        {
            if (fnmatch($pattern, $name))
            {
                return false;
            }
        }

        if (count($this->includePatterns) === 0)
        {
            return true;
        }

        foreach ($this->includePatterns as $pattern)
        {
            if (fnmatch($pattern, $name))
            {
                return true;
            }
        }

        return false;
    }

    /**
     * initialize include/exclude pattern
     */
    protected function initializePattern()
    {
        if ($this->includePatterns !== null && $this->excludePatterns !== null)
        {
            return;
        }

        $this->includePatterns = array();
        $this->excludePatterns = array();

        // Get 'extra' from RootPackage
        $extra = $this->composer->getPackage()->getExtra();

        if (isset($extra['shared']['include']))
        {
            if (is_array($extra['shared']['include']))
            {
                $this->includePatterns = $extra['shared']['include'];
            }
            else
            {
                $this->io->write("<warning>Warning: \"extra.shared.include\" should be array in composer.json</warning>");
            }
        }

        if (isset($extra['shared']['exclude']))
        {
            if (is_array($extra['shared']['exclude']))
            {
                $this->excludePatterns = $extra['shared']['exclude'];
            }
            else
            {
                $this->io->write("<warning>Warning: \"extra.shared.exclude\" should be array in composer.json</warning>");
            }
        }
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getSharedDir()
    {
        $sharedPath = getenv('COMPOSER_SHARED_DIR');

        if (strlen($sharedPath) === 0)
        {
            $sharedPath = $this->getComposerHome() . DIRECTORY_SEPARATOR . 'shared';
        }

        return $sharedPath;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getComposerHome()
    {
        $composerHome = $this->composer->getConfig()->get('home');

        if (strlen($composerHome) == 0)
        {
            throw new \RuntimeException('Unable get composer home');
        }

        return $composerHome;
    }

    /**
     * {@inheritDoc}
     */
    protected function updateCode(PackageInterface $initial, PackageInterface $target)
    {
        $initialShared = $this->isSharedInstallEnabled($initial);
        $targetShared = $this->isSharedInstallEnabled($target);

        if (!$initialShared && !$targetShared)
        {
            parent::updateCode($initial, $target);
            return;
        }

        $initialDownloadPath = $this->getInstallPath($initial);
        $targetDownloadPath = $this->getInstallPath($target);

        if ($targetDownloadPath !== $initialDownloadPath)
        {
            if (!$initialShared)
            {
                $this->removeCode($target);
            }

            $this->installCode($target);
            return;
        }

        $this->downloadManager->update($initial, $target, $targetDownloadPath);
    }

    /**
     * {@inheritDoc}
     */
    protected function removeCode(PackageInterface $package)
    {
        if (!$this->isSharedInstallEnabled($package))
        {
            parent::removeCode($package);
            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function generateWindowsProxyCode($bin, $link)
    {
        $code = parent::generateWindowsProxyCode($bin, $link);
        list ($first, $code) = explode("\r\n", $code, 2);
        $append = "SET COMPOSER_SHARED_AUTOLOAD_PATH=" . escapeshellarg($this->getAutoloadPath());
        return "$first\r\n$append\r\n$code";
    }

    /**
     * {@inheritDoc}
     */
    protected function generateUnixyProxyCode($bin, $link)
    {
        $code = parent::generateUnixyProxyCode($bin, $link);
        list ($first, $code) = explode("\n", $code, 2);
        $append = "export COMPOSER_SHARED_AUTOLOAD_PATH=" . escapeshellarg($this->getAutoloadPath());
        return "$first\n$append\n$code";
    }

    /**
     * {@inheritDoc}
     */
    protected function initializeVendorDir()
    {
        parent::initializeVendorDir();
        $this->dumpSharedAutoload();
    }

    protected function getAutoloadPath()
    {
        $filesystem = new Filesystem();
        $config = $this->composer->getConfig();
        $vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));
        return $vendorPath . '/autoload.php';
    }

    protected function dumpSharedAutoload()
    {
        $dst = $this->getSharedDir() . DIRECTORY_SEPARATOR . 'autoload.php';
        $src = __DIR__ . '/../../../resource/autoload.php';
        $this->filesystem->ensureDirectoryExists(dirname($dst));
        copy($src, $dst);
    }
}
