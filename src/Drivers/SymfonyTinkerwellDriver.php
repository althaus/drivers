<?php

use Tinkerwell\ContextMenu\Label;
use Tinkerwell\ContextMenu\OpenURL;
use Tinkerwell\ContextMenu\SetCode;
use Tinkerwell\ContextMenu\Submenu;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class SymfonyTinkerwellDriver extends TinkerwellDriver
{
    private $kernel;
    
    public function canBootstrap($projectPath)
    {
        return file_exists($projectPath . '/public/index.php') &&
            file_exists($projectPath . '/config/bootstrap.php') &&
            file_exists($projectPath . '/symfony.lock') &&
            file_exists($projectPath . '/bin/console');
    }

    public function bootstrap($projectPath)
    {
        require_once $projectPath . '/config/bootstrap.php';
        
        if ($_SERVER['APP_DEBUG']) {
            umask(0000);
        
            Symfony\Component\ErrorHandler\Debug::enable();
        }
        
        if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
            Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
        }
        
        if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
            Request::setTrustedHosts([$trustedHosts]);
        }
        
        $this->kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
        $this->kernel->boot();
    }

    public function contextMenu()
    {
        $version = Symfony\Component\HttpKernel\Kernel::VERSION;

        return [
            Label::create('Detected Symfony v' . $version),
            OpenURL::create('Documentation', "https://symfony.com/doc/{$version}/setup.html"),
        ];
    }

    public function getAvailableVariables()
    {
        return [
            '_kernel' => $this->kernel,
            '_container' => $this->kernel->getContainer(),
        ];
    }
}
