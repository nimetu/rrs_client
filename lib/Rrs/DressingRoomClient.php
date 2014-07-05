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

use Ryzom\Common\EGender;
use Ryzom\Common\EVisualSlot;
use Ryzom\Common\TPeople;

/**
 * Class DressingRoom
 *
 * @package Rrs
 */
class DressingRoomClient
{
    private $resourceRoot;

    private static $haircutArray;
    private static $haircutFixes;
    private static $sheetArray;

    /** @var string */
    private $race;

    /** @var string */
    private $gender;

    /** @var int */
    private $eyes;

    /** @var string */
    private $haircut;

    /** @var int */
    private $tattoo;

    /** @var int */
    private $angle;

    /** @var string[] */
    private $armor;

    /** @var int[] */
    private $gabarit;

    /** @var int[] */
    private $morph;

    /** @var array */
    private $hands;

    /** @var string */
    private $dataPath;

    /** @var int */
    private $width;

    /** @var int */
    private $height;

    /**
     * Dressing room
     */
    public function __construct($dataPath)
    {
        $this->dataPath = $dataPath;

        // same as RRS
        $this->width = 300;
        $this->height = 600;

        $this->initialize();
    }

    /**
     * @param Character $char
     *
     * @return string
     */
    public function render(Character $char)
    {
        $this->configure($char);

        $png = $this->getCanvas();
        $path = $this->dataPath.'/250x600';
        if (!isset($this->armor['head'])) {
            if ($this->angle == 0) {
                $this->mergeImage($png, $path.'/'.$this->tattoo);
            }
            // eyes 'back' view contains the neck part, so we need to render it either way
            $this->mergeImage($png, $path.'/'.$this->eyes);
            $this->mergeImage($png, $path.'/'.$this->haircut);
        }

        // TODO: do we must use right order to stack images?
        foreach ($this->armor as $image) {
            $this->mergeImage($png, $path.'/'.$image);
        }

        if ($char->isFaceShot()) {
            $png = $this->doFaceShot($char, $png);
        }

        return $this->outputAsPng($png);
    }

    /**
     * Prepare and return canvas for output image
     *
     * @return resource
     */
    private function getCanvas()
    {
        $png = imagecreatetruecolor($this->width, $this->height);
        imagesavealpha($png, true);

        $bgColor = imagecolorallocatealpha($png, 0, 0, 0, 127);
        imagefill($png, 0, 0, $bgColor);

        return $png;
    }

    /**
     * Return image as string
     *
     * @param resource $png
     *
     * @return string
     */
    private function outputAsPng($png)
    {
        ob_start();

        imagepng($png, null, 9);
        imagedestroy($png);

        return ob_get_clean();
    }

    /**
     * Normalize race string
     *
     * @param int $race
     *
     * @return null|string
     */
    private function normalizeRace($race)
    {
        switch ($race) {
            case TPeople::TRYKER:
                return 'tr';
            case TPeople::MATIS:
                return 'ma';
            case TPeople::FYROS:
                return 'fy';
            case TPeople::ZORAI:
                return 'zo';
            default:
                return 'fy';
        }
    }

    /**
     * Normalize gender string
     *
     * @param int $gender
     *
     * @return null|string
     */
    private function normalizeGender($gender)
    {
        switch ($gender) {
            case EGender::FEMALE:
                return 'f';
            case EGender::MALE:
                return 'm';
            default:
                return 'f';
        }
    }

    /**
     * Normalize haircut/color string
     *
     * @param int $index
     * @param int $color
     *
     * @return null|array
     */
    private function normalizeHaircut($index, $color)
    {
        // do visual index lookup
        if (isset(self::$haircutFixes[$index])) {
            $g = $this->gender == 'm' ? 0 : 1;
            $index = self::$haircutFixes[$index][$g];
        }
        $sheet = ryzom_vs_sheet(EVisualSlot::HEAD_SLOT, $index);
        // remove .sitem
        $sheet = substr($sheet, 0, -6);

        $failed = false;
        if (!preg_match('/(tr|fy|ma|zo)_ho(m|f)_hair_([^_]+)/i', $sheet, $match)) {
            // not a haircut, maybe helmet?
            return null;
        } else if ("{$this->race}_ho{$this->gender}_hair_" !== substr($sheet, 0, 12)) {
            // visual slot index is for different race/gender
            $failed = true;
        } else if (empty(self::$haircutArray[$sheet])) {
            // sheet is not in shapes array
            $failed = true;
        }

        if ($failed) {
            // fallback to 'basic01' shape
            list($index, $sheet) = $this->getDefaultHaircut($this->race, $this->gender);
            $sheet = substr($sheet, 0, -6);
        }

        $shape = self::$haircutArray[$sheet];
        return ['sheet' => $sheet, 'shape' => $shape, 'color' => $color, 'vs_index' => $index];
    }

    /**
     * @param Character $char
     */
    private function configure(Character $char)
    {
        $this->readAvatar($char);

        $this->readArmor($char);
    }

    /**
     * Read race, gender, eyes, haircut, tattoo
     *
     * @param Character $char
     */
    private function readAvatar(Character $char)
    {
        // angle=0&race=tr&gender=f&hair=99/0&tattoo=0&eyes=0
        $dir = $char->getDirection();
        $this->angle = (($dir >= 0 && $dir <= 90) || ($dir >= 270)) ? 0 : 1;

        $this->race = $this->normalizeRace($char->getRace());
        $this->gender = $this->normalizeGender($char->getGender());

        list($index, $color) = $char->getSlot(EVisualSlot::HEAD_SLOT);
        $haircut = $this->normalizeHaircut($index, $color);
        if ($haircut) {
            $this->haircut = $this->getHaircutFile(
                $this->race,
                $this->gender,
                $this->angle,
                $haircut['color'],
                $haircut['shape']
            );
        }

        list($tattoo, $eyeColor) = $char->getSlot(EVisualSlot::FACE_SLOT);
        $this->eyes = $this->getEyesFile($this->race, $this->gender, $this->angle, $eyeColor);
        $this->tattoo = $this->getTattooFile($this->race, $this->gender, $this->angle, $tattoo);
    }

    /**
     * Read equipped armor parts
     *
     * @param Character $char
     */
    private function readArmor(Character $char)
    {
        // head=igthu/0&chest=ictalv/1&arms=ictals/1&hands=ictalg/1&feet=ictalb/1&legs=ictalp/1&size=large
        $this->armor = [];

        // armor parts <sheet/color>
        $parts = [
            EVisualSlot::HEAD_SLOT => 'head',
            EVisualSlot::ARMS_SLOT => 'arms',
            EVisualSlot::HANDS_SLOT => 'hands',
            EVisualSlot::CHEST_SLOT => 'chest',
            EVisualSlot::FEET_SLOT => 'feet',
            EVisualSlot::LEGS_SLOT => 'legs'
        ];
        foreach ($parts as $slot => $key) {
            if ($this->haircut && $slot === EVisualSlot::HEAD_SLOT) {
                // we have haircut, so ignore this
                continue;
            }

            list($index, $color) = $char->getSlot($slot);
            $item = $this->lookupSlotItem($slot, $index);
            $image = $this->getArmorFile($this->race, $this->angle, $color, $item['grade'], $item['shape']);

            $this->armor[$key] = $image;
        }

        // TODO: sort parts same way javascript does
        //$sk = ['head' => 40, 'chest' => 30, 'legs' => 10, 'feet' => 20, 'arms' => 10, 'hands' => 5];
        // vest == 'm' and vest.type == 'l' -> vest = pants + 1 || vest = pants - 1
        // pants.type == 'c' -> boots = pants -1
    }

    /**
     * @param string $race
     * @param string $gender
     *
     * @return array
     */
    private function getDefaultHaircut($race, $gender)
    {
        $sheet = "{$race}_ho{$gender}_hair_basic01.sitem";
        $index = ryzom_vs_index(EVisualSlot::HEAD_SLOT, $sheet);

        return [$index, $sheet];
    }

    /**
     * @param string $avatar
     * @param string $gender
     * @param int $angle
     * @param int $color
     * @param string $shape
     *
     * @return string
     */
    private function getHaircutFile($avatar, $gender, $angle, $color, $shape)
    {
        // fy/000/h1/0/fy_hof_cheveux_artistic01.shape.png
        return sprintf('%s/%03d/h%d/%d/%s.png', $avatar, $angle, $color + 1, 0, $shape);
    }

    /**
     * @param string $avatar
     * @param string $gender
     * @param int $angle
     * @param int $color
     *
     * @return string
     */
    private function getEyesFile($avatar, $gender, $angle, $color)
    {
        // fy/000/e1/0/fy_hof_visage.shape.png
        $shape = sprintf('%s_ho%s_visage.shape', $avatar, $gender);

        return sprintf('%s/%03d/e%d/0/%s.png', $avatar, $angle, $color + 1, $shape);
    }

    /**
     * @param string $avatar
     * @param string $gender
     * @param int $angle
     * @param int $tattoo
     *
     * @return string
     */
    private function getTattooFile($avatar, $gender, $angle, $tattoo)
    {
        // fy/000/makeup/0/fy_hof_fy_visage_000_makeup00.png
        $shape = sprintf('%s_ho%s_%1$s_visage_%03d_makeup%02d', $avatar, $gender, $angle, $tattoo);

        return sprintf('%s/%03d/makeup/0/%s.png', $avatar, $angle, $shape);
    }

    /**
     * @param string $race
     * @param int $angle
     * @param int $color
     * @param int $grade
     * @param string $shape
     *
     * @return string
     */
    private function getArmorFile($race, $angle, $color, $grade, $shape)
    {
        // 'fy/000/u1/0/ca_hof_armor01_hum_armpad.shape.png'
        return sprintf('%s/%03d/u%d/%d/%s.png', $race, $angle, $color + 1, $grade, $shape);
    }

    /**
     * Load haircuts and sheets cache
     */
    private function initialize()
    {
        if (self::$haircutFixes === null) {
            self::$haircutFixes = include __DIR__.'/Resources/haircut-fixes.php';
        }
        if (self::$haircutArray === null) {
            self::$haircutArray = include __DIR__.'/Resources/haircut-shapes.php';
        }
        if (self::$sheetArray === null) {
            self::$sheetArray = include __DIR__.'/Resources/item-shapes.php';
        }
    }

    /**
     * Load $srcFile into $png image
     *
     * @param resource $png
     * @param string $srcFile
     *
     * @return bool
     */
    private function mergeImage($png, $srcFile)
    {
        if (!file_exists($srcFile)) {
            //echo "- ($srcFile) not found\n";
            return false;
        }
        $src = imagecreatefrompng($srcFile);
        $srcw = imagesx($src);
        $srch = imagesy($src);
        // padding +25,+0 - dressing room images are 250x600
        $padx = ($this->width - $srcw) / 2;
        $pady = ($this->height - $srch) / 2;
        imagecopy($png, $src, $padx, $pady, 0, 0, $srcw, $srch);
        imagedestroy($src);
    }

    /**
     * @param int $slot
     * @param int $index
     *
     * @return array
     */
    private function lookupSlotItem($slot, $index)
    {
        $sheet = ryzom_vs_sheet($slot, $index);
        if ($sheet === false) {
            // invalid item for slot, use default
        }
        if (!$sheet) {
            var_dump($slot, $index, $sheet);
            die();
        }
        $sheet = substr($sheet, 0, -6);
        $grade = self::$sheetArray[$sheet]['grade'];
        if ($this->gender == 'f') {
            $shape = self::$sheetArray[$sheet]['femaleShape'];
        } else {
            $shape = self::$sheetArray[$sheet]['maleShape'];
        }
        return [
            'sheet' => $sheet,
            'shape' => $shape,
            'grade' => $grade,
        ];
    }

    private function doFaceShot(Character $char, $img)
    {
        switch ($char->getRace()) {
            case TPeople::FYROS:
                $crop = [
                    0 => [143, 157],
                    1 => [149, 180],
                ];
                break;
            case TPeople::TRYKER:
                $crop = [
                    0 => [143, 191],
                    1 => [148, 207],
                ];
                break;
            case TPeople::MATIS:
                $crop = [
                    0 => [144, 138],
                    1 => [148, 170],
                ];
                break;
            case TPeople::ZORAI:
                $crop = [
                    0 => [143, 109],
                    1 => [149, 122],
                ];
                break;
            default:
                // should not reach here
                return $img;
        }
        $gender = $char->getGender();
        list($cx, $cy) = $crop[$gender];

        $x = $cx - 50;
        $y = $cy - 50;
        $w = 100;
        $h = 100;

        if ($char->isCropFaceShot()) {
            // WxW box
            $out = imagecreatetruecolor($this->width, $this->width);
            $dstw = $this->width;
            $dsth = $this->width;
        } else {
            // portrait
            $out = imagecreatetruecolor($this->width, $this->height);
            $dstw = $this->width;
            $dsth = $this->height;
            $y -= $h / 2;
            $h += $h;
        }
        $bg = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $bg);
        imagesavealpha($out, true);
        imagecopyresampled(
            $out,
            $img,
            0,
            0,
            $x,
            $y,
            $dstw,
            $dsth,
            $w,
            $h
        );
        imagedestroy($img);

        return $out;
    }
}
