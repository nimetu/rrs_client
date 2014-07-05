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
 * Socket wrapper class around stream_socket_client()
 */
class Socket implements SocketInterface
{

    /** @var string */
    protected $uri;

    /** @var int */
    protected $timeout;

    /** @var bool */
    protected $blocking;

    /** @var resource */
    protected $sock;

    /**
     * Uri needs to be in 'tcp://127.0.0.1:12345' format
     *
     * Socket is only connected when there is read or write
     * Connection can be forced calling connect()
     *
     * @param string $uri
     * @param int $timeout
     * @param bool $blocking
     */
    public function __construct($uri, $timeout = 2, $blocking = true)
    {
        $this->uri = $uri;
        $this->timeout = $timeout;
        $this->blocking = $blocking;

        $this->sock = null;
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        if ($this->sock) {
            return;
        }
        $this->sock = stream_socket_client(
            $this->uri,
            $errno,
            $errstr,
            $this->timeout
        );

        if (!$this->sock) {
            throw new \RuntimeException("Can't connect to the server at '{$this->uri}' ($errno: $errstr)");
        }

        stream_set_timeout($this->sock, $this->timeout);
        stream_set_blocking($this->sock, $this->blocking);
        stream_set_read_buffer($this->sock, 0); // 64k
        stream_set_write_buffer($this->sock, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if ($this->sock) {
            fclose($this->sock);
            $this->sock = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($bytes)
    {
        if (!$this->sock) {
            $this->connect();
        }
        $buffer = '';
        $prevTimer = microtime(true);
        while ($bytes > 0) {
            $chRead = fread($this->sock, $bytes);
            $nbRead = strlen($chRead);
            $buffer .= $chRead;

            $timerNow = microtime(true);
            $diff = $timerNow - $prevTimer;
            if ($nbRead == 0) {
                if ($diff > $this->timeout) {
                    $info = stream_get_meta_data($this->sock);
                    if ($info['timed_out']) {
                        throw new \RuntimeException("Socket read timed out");
                    }
                    break;
                }
            } else {
                $prevTimer = $timerNow;
            }

            $bytes -= $nbRead;
        }
        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function write($buffer)
    {
        if (!$this->sock) {
            $this->connect();
        }
        $bytes = fwrite($this->sock, $buffer);
        if ($bytes === false) {
            throw new \RuntimeException('Error writing to socket');
        }
        fflush($this->sock);
    }
}
