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
 * CallbackClient
 */
class CallbackClient
{
    /** @var SocketInterface */
    private $sock;

    /** @var int */
    private $msgNum;

    /**
     * Open new TCP socket to addr:port
     *
     * @param SocketInterface $sock
     */
    public function __construct(SocketInterface $sock)
    {
        $this->msgNum = 0;

        $this->sock = $sock;
    }

    /**
     * Send message
     *
     * @param StreamInterface $message
     */
    public function sendMessage(StreamInterface $message)
    {
        // TFormat from <nel/message.h>
        $messageType = 0;

        // build packet
        // <int32> packet length (length itself is not counted)
        // <int32> packet number
        // <byte>  message type
        // <.....> message

        // first pass - to get packet length
        $hd = new MemStream();
        $hd->serial_uint32($this->msgNum);
        $hd->serial_byte($messageType);
        // include original message
        $message->serial($hd);

        $this->msgNum++;

        $buf = $hd->getBuffer();
        $len = strlen($buf);

        // second pass - final packet
        $hd = new MemStream();
        $hd->serial_uint32_n($len);
        $hd->serial_buffer($buf);

        $this->sock->write($hd->getBuffer());
    }

    /**
     * Waits message from server
     *
     * @param StreamInterface $message
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function waitMessage(StreamInterface $message)
    {
        // packet format
        // <int32>   packet length (length itself is not counted)
        // <byte>[5] ignore
        // <.....> message
        $buffer = $this->sock->read(4);

        $s = new MemStream($buffer);
        $s->serial_uint32_n($size);

        $buffer = $this->sock->read($size);
        $len = strlen($buffer);

        if ($size != $len) {
            throw new \RuntimeException("Partial packet received (got $len, expected $size)");
        }

        // discard 5 first bytes
        // <uint32> 'zeroValue=123'
        // <byte>   TFormat (bitfield)
        $buffer = substr($buffer, 5);

        $message->serial(new MemStream($buffer));
    }
}
