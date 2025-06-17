<?php
namespace Concrete\Package\MsvCloudflareTurnstile;

use Concrete\Core\Database\EntityManager\Provider\ProviderInterface;
use Concrete\Core\Captcha\Library as CaptchaLibrary;
use Concrete\Core\Package\Package;

class Controller extends Package implements ProviderInterface
{
    protected $pkgHandle = 'msv_cloudflare_turnstile';
    protected $appVersionRequired = '8.5.0';
    protected $pkgVersion = '1.0.2';

    protected $pkgAutoloaderRegistries = [
        'src/Captcha' => 'Concrete\Package\MsvCloudflareTurnstile\Captcha',
    ];

    public function getPackageName()
    {
        return t('Captcha by Cloudflare Turnstile');
    }

    public function getPackageDescription()
    {
        return t('Provides a Cloudflare Turnstile Captcha field.');
    }

    public function install()
    {
        $pkg = parent::install();
        CaptchaLibrary::add('turnstile', t('Cloudflare Turnstile'), $pkg);
        return $pkg;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Database\EntityManager\Provider\ProviderInterface::getDrivers()
     */
    public function getDrivers()
    {
        return [];
    }
}
