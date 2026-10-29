<?php
/**
 * Copyright © FishPig Limited
 */
declare(strict_types=1);

namespace FishPig\Smtp\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use FishPig\Smtp\Model\Config\Source\MailProvider;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\TransportInterface;


class Config
{
    /**
     * @const string
     */
    const CONFIG_BASE = 'fishpig_smtp';

    /**
     *
     */
    private $scopeConfig = null;

    /**
     *
     */
    private $mailProvider = null;

    /**
     *
     */
    private $encryptor = null;

    /**
     * @param ScopeConfigInterface $scopeConfig Core store config
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        MailProvider $mailProvider,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->mailProvider = $mailProvider;
        $this->encryptor = $encryptor;
    }

    /**
     * @return bool
     */
    public function isEnabled() : bool
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_BASE . '/settings/enabled');
    }

    /**
     * @return int
     */
    public function getMailProvider() : int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        $mailProvider = (int)$this->scopeConfig->getValue(self::CONFIG_BASE . '/settings/mail_provider');

        if ($this->mailProvider->isValidMailProvider($mailProvider)) {
            return $mailProvider;
        }

        return 0;
    }

    /**
     * SMTP connections use implicit TLS on port 465, and STARTTLS on other ports when the server offers it.
     */
    public function getConfiguredTransport(): TransportInterface
    {
        switch ($this->getMailProvider()) {
            case MailProvider::TYPE_GOOGLE:
                return $this->createSmtpTransport(
                    'smtp.gmail.com',
                    587,
                    $this->getProviderUsername('google'),
                    $this->getProviderPassword('google')
                );
            case MailProvider::TYPE_OFFICE365:
                return $this->createSmtpTransport(
                    'smtp.office365.com',
                    587,
                    $this->getProviderUsername('office365'),
                    $this->getProviderPassword('office365')
                );
            case MailProvider::TYPE_SMTP:
                return $this->createSmtpTransport(
                    $this->getProviderConfig('smtp', 'host'),
                    (int)$this->getProviderConfig('smtp', 'port'),
                    $this->getProviderUsername('smtp'),
                    $this->getProviderPassword('smtp')
                );
            default:
                return (new NativeTransportFactory())->create(new Dsn('native', 'default'));
        }
    }

    /**
     * @return TransportInterface
     */
    private function createSmtpTransport(string $host, int $port, string $username, string $password): TransportInterface
    {
        return (new EsmtpTransportFactory())->create(
            new Dsn('smtp', $host, $username, $password, $port)
        );
    }

    /**
     * @return string
     */
    private function getProviderConfig(string $provider, string $field) : string
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_BASE . '/provider_' . $provider . '/' . $field
        );
    }

    /**
     * @return string
     */
    private function getProviderUsername(string $provider) : string
    {
        return $this->throwIfEmpty(
            $this->getProviderConfig($provider, 'username'),
            $provider . '.username'
        );
    }

    /**
     * @return string
     */
    private function getProviderPassword(string $provider) : string
    {
        return $this->encryptor->decrypt(
            $this->throwIfEmpty(
                $this->getProviderConfig($provider, 'password'),
                $provider . '.password'
            )
        );
    }

    /**
     * @param  string $str
     * @param  string $field
     * @return string
     */
    private function throwIfEmpty($str, $field)
    {
        if (($str = trim($str)) === '') {
            throw new \Exception('Empty value found in ' . $field);
        }

        return $str;
    }

    /**
     * @return bool
     */
    public function isSetReturnPath() : bool
    {
        return $this->scopeConfig->isSetFlag(
            \Magento\Email\Model\Transport::XML_PATH_SENDING_SET_RETURN_PATH
        );
    }
}
