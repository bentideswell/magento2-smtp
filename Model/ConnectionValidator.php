<?php
/**
 *
 */
namespace FishPig\Smtp\Model;

class ConnectionValidator
{
    /**
     *
     */
    private $config = null;

    /**
     *
     */
    private $smtpTransportFactory = null;

    /**
     *
     */
    public function __construct(
        \FishPig\Smtp\Model\Config $config,
        \Laminas\Mail\Transport\SmtpFactory $smtpTransportFactory
    ) {
        $this->config = $config;
        $this->smtpTransportFactory = $smtpTransportFactory;
    }

    /**
     *
     */
    public function validate(bool $throw = false): bool
    {
        try {
            $transport = $this->config->getConfiguredTransport();
            return method_exists($transport, 'isConnected')
                   && $transport->isConnected();
        } catch (\Exception $e) {
            if ($throw) {
                throw $e;
            }
        }

        return false;
    }
}
