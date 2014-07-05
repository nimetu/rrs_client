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

/**
 * SocketInterface
 */
interface SocketInterface
{

    /**
     * Open connection
     */
    public function connect();

    /**
     * Close connection
     */
    public function close();

    /**
     * Read up to N bytes from socket
     *
     * @param int $bytes
     *
     * @return string
     * @throws \RuntimeException
     */
    public function read($bytes);

    /**
     * Write to socket
     *
     * @param string $buffer
     *
     * @throws \RuntimeException
     */
    public function write($buffer);
}
