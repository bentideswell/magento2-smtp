<?php
/**
 *
 */
namespace FishPig\Smtp\Model;

use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

class ConnectionValidator
{
    /**
     *
     */
    private $config = null;

    /**
     *
     */
    public function __construct(
        \FishPig\Smtp\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     *
     */
    public function validate(bool $throw = false): bool
    {
        try {
            $transport = $this->config->getConfiguredTransport();

            if (!$transport instanceof SmtpTransport) {
                return false;
            }

            $transport->start();
            $transport->stop();

            return true;
        } catch (\Exception $e) {
            if ($throw) {
                throw $e;
            }
        }

        return false;
    }
}
