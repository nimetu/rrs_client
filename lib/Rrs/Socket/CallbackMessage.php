<?php
/*
 * RyzomRenderingService - https://github.com/nimetu/rrs_client.git
 * Copyright (c) 2014 Meelis Mägi <nimetu@gmail.com>
 *
 * This file is part of RyzomRenderingService.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rrs\Socket;

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;

/**
 * CallbackMessage
 */
class CallbackMessage implements StreamInterface
{

    /** @var string */
    protected $name;

    /** @var string */
    protected $payload;

    /**
     * Create new message
     * If name is left empty, it's inbound message, else outbound
     *
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
        $this->payload = '';
    }

    /**
     * Get message name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set message payload
     *
     * @param string $payload
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    /**
     * Get message payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * {@inheritdoc}
     */
    public function serial(MemStream $s)
    {
        // string with <uint32> length counter
        $s->serial_string($this->name);

        if ($s->isReading()) {
            // read all remaining bytes from buffer
            $length = $s->getSize() - $s->getPos();
        } else {
            $length = strlen($this->payload);
        }
        $s->serial_buffer($this->payload, $length);
    }

}
