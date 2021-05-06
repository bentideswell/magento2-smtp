<?php
/**
 * Copyright © FishPig Limited
 */
declare(strict_types=1);

namespace FishPig\Smtp\Model;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Mail\MessageInterface $message,
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
            $laminasMessage = \Laminas\Mail\Message::fromString(
                $this->getMessage()->getRawMessage()
            )->setEncoding('utf-8');

            if ($this->config->isSetReturnPath() && $laminasMessage->getFrom()->count()) {
                $fromAddressList = $laminasMessage->getFrom();
                $fromAddressList->rewind();
                $laminasMessage->setSender($fromAddressList->current()->getEmail());
            }

            $this->transport->send($laminasMessage);
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
