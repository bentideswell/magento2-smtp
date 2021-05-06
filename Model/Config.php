<?php
/**
 * Copyright © FishPig Limited
 */
declare(strict_types=1);

namespace FishPig\Smtp\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use FishPig\Smtp\Model\Config\Source\MailProvider;
use Laminas\Mail\Transport\Sendmail;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mail\Transport\SmtpOptions;


class Config
{
    /**
     * @const string
     */
    const CONFIG_BASE = 'fishpig_smtp';

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
     *
     */
    public function getConfiguredTransport()
    {
        switch ($this->getMailProvider()) {
            case MailProvider::TYPE_GOOGLE:
                return new SmtpTransport(
                    new SmtpOptions(
                        [
                            'name' => 'gmail',
                            'host' => 'smtp.gmail.com',
                            'port' => 587,
                            'connection_class' => 'login',
                            'connection_config' => [
                                'username' => $this->getGoogleUsername(),
                                'password' => $this->getGooglePassword(),
                                'ssl' => 'tls',
                                'port' => 587
                            ]
                        ]
                    )
                );
                break; 
            default:
                return new Sendmail();
                break;
        }
    }
    
    /**
     * @return string
     */
    private function getGoogleUsername() : string
    {
        return $this->throwIfEmpty(
            $this->scopeConfig->getValue(self::CONFIG_BASE . '/provider_google/username'),
            'google.username'
        );
    }

    /**
     * @return string
     */
    private function getGooglePassword() : string
    {
        return $this->encryptor->decrypt(
            $this->throwIfEmpty(
                $this->scopeConfig->getValue(self::CONFIG_BASE . '/provider_google/password'),
                'google.password'
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
