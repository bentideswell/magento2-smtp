<?php
/**
 * Copyright © FishPig Limited
 */
declare(strict_types=1);

namespace FishPig\Smtp\Model;

use Symfony\Component\Mailer\Mailer;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     *
     */
    private $config = null;

    /**
     *
     */
    private $message = null;

    /**
     *
     */
    private $transport = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Mail\EmailMessageInterface $message,
        \FishPig\Smtp\Model\Config $config
    ) {
        $this->config = $config;
        $this->message = $message;
        $this->transport = $this->config->getConfiguredTransport();
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        try {
            if (!$this->config->isEnabled()) {
                throw new \Exception('SMTP module is disabled.');
            }

            $email = $this->getMessage()->getSymfonyMessage();
            $headers = $email->getHeaders();

            if ($this->config->isSetReturnPath() && !$headers->has('Sender')) {
                $fromAddresses = $headers->get('From')?->getAddresses();

                if ($fromAddresses) {
                    $headers->addMailboxHeader('Sender', reset($fromAddresses));
                }
            }

            (new Mailer($this->transport))->send($email);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\MailException(__($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
