<?php
/**
 *
 */
namespace FishPig\Smtp\Model\Transport;

class Smtp extends \Laminas\Mail\Transport\Smtp
{
    /**
     *
     */
    public function isConnected(): bool
    {
        try {
            return is_object($this->connect());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}