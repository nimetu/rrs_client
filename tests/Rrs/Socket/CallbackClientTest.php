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

use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-06-02 at 14:15:24.
 */
class CallbackClientTest extends TestCase
{

    public function testSendMessage()
    {
        // <int32> packet length
        $packet = "\x00\x00\x00\x15";
        // <int32> msg number, <byte> type
        $packet .= "\x00\x00\x00\x00\x00";
        // CallbackMessage packet
        $packet .= "\x05\x00\x00\x00WRITEpayload";

        /** var $service SocketInterface */
        $service = $this->createMock(\Rrs\Socket\SocketInterface::class);
        $service->expects($this->once())
            ->method('write')
            ->with($packet);

        $msgOut = new CallbackMessage('WRITE');
        $msgOut->setPayload('payload');

        $client = new CallbackClient($service);
        $client->sendMessage($msgOut);
    }

    public function testWaitMessage()
    {
        // read() is called twice
        // + read 4 bytes to get size
        // + read remaining bytes and discard first 5
        // because of this, packet format is specially crafted
        $packet = "".
            "\x00\x00\x00\x14". // packet size (15 + 5)
            "\x00". // ignore
            "\x04\x00\x00\x00"."READ". // name
            "payload"; // message

        $service = $this->createMock(\Rrs\Socket\SocketInterface::class);
        $service->expects($this->any())
            ->method('read')
            ->willReturn($packet);

        $msgIn = new CallbackMessage();

        $client = new CallbackClient($service);
        $client->waitMessage($msgIn);

        $this->assertEquals('READ', $msgIn->getName());
        $this->assertEquals('payload', $msgIn->getPayload());
    }

    public function testWaitMessageException()
    {
        // short packet for CallbackClient,
        // so that exception is thrown
        $packet = "".
            "\x00\x00\x00\x10".
            "\x00".
            "\x04\x00\x00\x00"."READ";

        $service = $this->createMock(\Rrs\Socket\SocketInterface::class);
        $service->expects($this->any())
            ->method('read')
            ->willReturn($packet);

        $msgIn = new CallbackMessage();

        $client = new CallbackClient($service);

        $this->expectException(
            '\RuntimeException',
            'Partial packet received (got 13, expected 16)'
        );
        $client->waitMessage($msgIn);
    }
}
