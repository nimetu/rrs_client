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

namespace Rrs;

use Rrs\Socket\CallbackClient;
use Rrs\Socket\CallbackMessage;
use Rrs\Socket\Socket;

/**
 * Client for ryzom renderer service
 */
class RrsClient
{
    /** @var Socket */
    protected $socket;

    /**
     * @param Socket $socket
     */
    public function __construct($socket)
    {
        $this->socket = $socket;
    }

    /**
     * @param Character $char
     *
     * @return bool|string
     */
    public function render(Character $char)
    {
        $socket = new CallbackClient($this->socket);

        // outgoing packet
        $msg = new CallbackMessage('RENDER');
        $msg->setPayload($char->getBuffer());
        $socket->sendMessage($msg);

        // incoming packet
        $msg = new CallbackMessage();

        try {
            $socket->waitMessage($msg);
        } catch (\RuntimeException $ex) {
            // received partial message
            // message is not decoded, so getName() will return empty string
        }

        if ($msg->getName() == 'PNG') {
            $result = $msg->getPayload();
            if ($char->isFaceShot()) {
                $result = $this->doFaceShot($char, $result);
            }
        } else {
            $result = false;
        }

        return $result;
    }

    private function doFaceShot(Character $char, $img)
    {
        if (!$char->isCropFaceShot()) {
            // nothing do to, image already in correct format
            return $img;
        }
        $png = imagecreatefromstring($img);
        $w = imagesx($png);
        $h = imagesy($png);

        // create new WxW image for face
        $out = imagecreatetruecolor($w, $w);
        $bg = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $bg);
        imagesavealpha($out, true);

        $top = max(0, ($h / 2) - ($w / 2));
        imagecopyresampled($out, $png, 0, 0, 0, $top, $w, $w, $w, $w);

        ob_start();
        imagepng($out, null, 9);
        $result = ob_get_clean();

        imagedestroy($png);
        imagedestroy($out);
        return $result;
    }
}
