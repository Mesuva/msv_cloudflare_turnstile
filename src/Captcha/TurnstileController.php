<?php

namespace Concrete\Package\MsvCloudflareTurnstile\Captcha;

use Psr\Log\LogLevel;
use Concrete\Core\View\View;
use Concrete\Core\Http\Request;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Http\Client\Client;
use Concrete\Core\Logging\LoggerAwareTrait;
use Concrete\Core\Captcha\CaptchaInterface;
use Concrete\Core\Controller\AbstractController;
use Concrete\Core\Logging\LoggerAwareInterface;

class TurnstileController extends AbstractController implements CaptchaInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
        parent::__construct();
    }

    public function getLoggerChannel()
    {
        return Channels::CHANNEL_SPAM;
    }

    public function display()
    {
        $config = app()->make('config');
        $sitekey = $config->get('msv_cloudflare_turnstile.turnstile.site_key', '');
        if ($sitekey) {
            $size = $config->get('msv_cloudflare_turnstile.turnstile.size', 'normal');
            $theme = $config->get('msv_cloudflare_turnstile.turnstile.theme', 'auto');
            $appearance = $config->get('msv_cloudflare_turnstile.turnstile.appearance', 'always');
            echo '<div class="cf-turnstile" data-sitekey="' . $sitekey . '" data-size="' . $size . '"  data-theme="' . $theme . '" data-appearance="' . $appearance . '"></div>';
        }
    }

    public function showInput()
    {
        $js = '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
        View::getInstance()->addHeaderItem($js);
    }

    public function label()
    {
        return '';
    }

    public function check()
    {
        $config = app()->make('config');
        $secret = $config->get('msv_cloudflare_turnstile.turnstile.secret_key', '');
        if (!$secret) {
            return false;
        }
        $request = app()->make(\Concrete\Core\Http\Request::class);
        $remote_addr = method_exists($request, 'getClientIp') ? $request->getClientIp() : $request->server->get('REMOTE_ADDR');
        $cf_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $token = $request->request->get('cf-turnstile-response');

        // Request data
        $data = [
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $remote_addr
        ];

        $client = $this->client;

        $version = app()->make('config')->get('concrete.version');
        if (version_compare($version, '9.0', '<')) {
            $client->setMethod('post');
            $client->setParameterPost(  $data);
            $client->setUri($cf_url);
            $client->setOptions([
                'timeout' => 5,
            ]);
            $response = $client->send();
        } else {
            $response = $client->request(Request::METHOD_POST, $cf_url, ['json' => $data]);
        }

        if ($response->getStatusCode() != 200) {
            $error_message = $response->getReasonPhrase();
            $logLevel = LogLevel::ERROR;
            $this->logger->log($logLevel, t('Cloudflare Turnstile failed to perform check: %s', $error_message));
            
            return false;
        }
        $response = json_decode($response->getBody(), true);
        if (!empty($response['error-codes'])) {
            if ($config->get('msv_cloudflare_turnstile.turnstile.log_failed', false)) {
                 $logLevel = LogLevel::NOTICE;
                 $this->logger->log($logLevel, t('Cloudflare Turnstile check failed. Error codes: %s', implode(', ', $response['error-codes'])));
            }
            return false;
        }

        return true;
    }

    public function saveOptions($data)
    {
        $data = (is_array($data) ? $data : []) + [
            'site_key' => '',
            'secret_key' => '',
            'size' => '',
            'theme' => '',
            'appearance' => '',
            'log_failed' => false,
        ];
        $config = $this->app->make('config');
        $config->save('msv_cloudflare_turnstile.turnstile.site_key', (string) $data['site_key']);
        $config->save('msv_cloudflare_turnstile.turnstile.secret_key', (string) $data['secret_key']);
        $config->save('msv_cloudflare_turnstile.turnstile.size', (string) $data['size']);
        $config->save('msv_cloudflare_turnstile.turnstile.theme', (string) $data['theme']);
        $config->save('msv_cloudflare_turnstile.turnstile.appearance', (string) $data['appearance']);
        $config->save('msv_cloudflare_turnstile.turnstile.log_failed', filter_var($data['log_failed'], FILTER_VALIDATE_BOOLEAN));
    }
}
