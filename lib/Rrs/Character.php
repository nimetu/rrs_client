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

use Nel\Misc\MemStream;
use Nel\Misc\StreamInterface;
use Ryzom\Common\EVisualSlot;
use Ryzom\Common\SPropVisualA;
use Ryzom\Common\SPropVisualB;
use Ryzom\Common\SPropVisualC;

/**
 * Renderer packet format
 */
class Character implements StreamInterface
{
    protected $version;
    protected $pktFormat;

    protected $race;
    protected $age;
    protected $direction;
    protected $angle;

    /** @var SPropVisualA */
    protected $vpa;
    /** @var SPropVisualB */
    protected $vpb;
    /** @var SPropVisualC */
    protected $vpc;

    protected $background;
    protected $FaceShot;
    protected $useFx;

    /** not serialized */
    protected $CropFaceShot;

    /**
     * CVisualMessage
     */
    public function __construct()
    {
        $this->version = 2;
        $this->pktFormat = 0;
        $this->race = 0;
        $this->age = 0;
        $this->direction = 0;
        $this->angle = 0;

        $this->FaceShot = false;
        $this->CropFaceShot = true;

        $this->vpa = new SPropVisualA();
        $this->vpb = new SPropVisualB();
        $this->vpc = new SPropVisualC();

        $this->background = 0;
        $this->useFx = false;
    }

    /**
     * Directly set VPropVisualA 64bit value
     *
     * @param string $vpa
     */
    public function setVpa($vpa)
    {
        $this->vpa->setValue($vpa);
    }

    /**
     * Get VPropVisualA 64bit value after char is built
     *
     * @param bool $hex
     *
     * @return int|string
     */
    public function getVpa($hex = false)
    {
        if ($hex) {
            return $this->vpa->getValueHex();
        }
        return $this->vpa->getValue();
    }

    /**
     * Directly set VPropVisualB 64bit value
     *
     * @param string $vpb
     */
    public function setVpb($vpb)
    {
        $this->vpb->setValue($vpb);
    }

    /**
     * Get VPropVisualB 64bit value after char is built
     *
     * @param bool $hex
     *
     * @return int|string
     */
    public function getVpb($hex = false)
    {
        if ($hex) {
            return $this->vpb->getValueHex();
        }

        return $this->vpb->getValue();
    }


    /**
     * Directly set VPropVisualC 64bit value
     *
     * @param string $vpc
     */
    public function setVpc($vpc)
    {
        $this->vpc->setValue($vpc);
    }

    /**
     * Get VPropVisualC 64bit value after char is built
     *
     * @param bool $hex
     *
     * @return int|string
     */
    public function getVpc($hex = false)
    {
        if ($hex) {
            return $this->vpc->getValueHex();
        }

        return $this->vpc->getValue();
    }

    /**
     * Set background color
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @param int $a
     */
    public function setBackground($r, $g, $b, $a)
    {
        $this->background = ($r << 24) + ($g << 16) + ($b << 8) + $a;
    }

    /**
     * @return int
     */
    public function getBackground()
    {
        return $this->background;
    }

    /**
     * Take head shot
     *
     * @param bool $v
     * @param bool $crop
     */
    public function setFaceShot($v, $crop = true)
    {
        $this->FaceShot = $v;
        $this->CropFaceShot = $crop;
    }

    /**
     * @return bool
     */
    public function isFaceShot()
    {
        return $this->FaceShot;
    }

    /**
     * @return bool
     */
    public function isCropFaceShot()
    {
        return $this->CropFaceShot;
    }

    /**
     * Race value from TPeople
     *
     * @param int $race
     */
    public function setRace($race)
    {
        $this->race = $race;
    }

    /**
     * @return int
     */
    public function getRace()
    {
        return $this->race;
    }

    /**
     * Gender value from EGender
     *
     * @param int $gender
     */
    public function setGender($gender)
    {
        $this->vpa->Sex = $gender;
    }

    /**
     * @return int
     */
    public function getGender()
    {
        return $this->vpa->Sex;
    }

    /**
     * Set characters age 0, 1 or 2
     *
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set character facing direction in 360 degree
     * 0 is front, 180 is back
     *
     * @param int $direction
     */
    public function setDirection($direction)
    {
        $direction = fmod($direction, 360);
        if ($direction < 0) {
            $direction += 360;
        }
        $this->direction = $direction;
    }

    /**
     * @return int
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Set character standing angle
     *
     * @param int $angle
     */
    public function setAngle($angle)
    {
        $this->angle = $angle;
    }

    /**
     * @return int
     */
    public function getAngle()
    {
        return $this->angle;
    }

    /**
     * Set character morph targets
     *
     * Each value is between 0 .. 7
     * Renderer converts it to 0 .. 1 range to modify
     * base blend min/max value race/gender has
     *
     * @param array $values [MorphTarget1 .. MorphTarget8]
     */
    public function setMorph(array $values)
    {
        $arr = array_merge($values, array(0, 0, 0, 0, 0, 0, 0, 0));
        $this->vpc->MorphTarget1 = max(0, min(7, $arr[0]));
        $this->vpc->MorphTarget2 = max(0, min(7, $arr[1]));
        $this->vpc->MorphTarget3 = max(0, min(7, $arr[2]));
        $this->vpc->MorphTarget4 = max(0, min(7, $arr[3]));
        $this->vpc->MorphTarget5 = max(0, min(7, $arr[4]));
        $this->vpc->MorphTarget6 = max(0, min(7, $arr[5]));
        $this->vpc->MorphTarget7 = max(0, min(7, $arr[6]));
        $this->vpc->MorphTarget8 = max(0, min(7, $arr[7]));
    }

    /**
     * @return int[]
     */
    public function getMorph()
    {
        return [
            $this->vpc->MorphTarget1,
            $this->vpc->MorphTarget2,
            $this->vpc->MorphTarget3,
            $this->vpc->MorphTarget4,
            $this->vpc->MorphTarget5,
            $this->vpc->MorphTarget6,
            $this->vpc->MorphTarget7,
            $this->vpc->MorphTarget8,
        ];
    }

    /**
     * Set character height/width/size
     *
     * Each value is between 0 .. 14
     * Renderer converts it to -1 .. +1 range to modify
     * base skeleton value race/gender has
     *
     * @param array $values [height, torso, arms, legs, breast]
     */
    public function setGabarit(array $values)
    {
        $arr = array_merge($values, array(0, 0, 0, 0, 0));
        $this->vpc->CharacterHeight = max(0, min(14, $arr[0]));
        $this->vpc->TorsoWidth = max(0, min(14, $arr[1]));
        $this->vpc->ArmsWidth = max(0, min(14, $arr[2]));
        $this->vpc->LegsWidth = max(0, min(14, $arr[3]));
        $this->vpc->BreastSize = max(0, min(14, $arr[4]));
    }

    /**
     * @return int[]
     */
    public function getGabarit()
    {
        return [
            $this->vpc->CharacterHeight,
            $this->vpc->TorsoWidth,
            $this->vpc->ArmsWidth,
            $this->vpc->LegsWidth,
            $this->vpc->BreastSize,
        ];
    }

    /**
     * Equip armor, item, haircut
     *
     * @param int $slot
     * @param int $index
     * @param int $color
     */
    public function setSlot($slot, $index, $color = 0)
    {
        switch ($slot) {
            case EVisualSlot::ARMS_SLOT:
                $this->vpa->ArmModel = $index;
                $this->vpa->ArmColor = $color;
                break;
            case EVisualSlot::LEGS_SLOT:
                $this->vpa->TrouserModel = $index;
                $this->vpa->TrouserColor = $color;
                break;
            case EVisualSlot::HANDS_SLOT:
                $this->vpb->HandsModel = $index;
                $this->vpb->HandsColor = $color;
                break;
            case EVisualSlot::FEET_SLOT:
                $this->vpb->FeetModel = $index;
                $this->vpb->FeetColor = $color;
                break;
            case EVisualSlot::CHEST_SLOT:
                $this->vpa->JacketModel = $index;
                $this->vpa->JacketColor = $color;
                break;
            case EVisualSlot::HEAD_SLOT:
                $this->vpa->HatModel = $index;
                $this->vpa->HatColor = $color;
                break;
            case EVisualSlot::FACE_SLOT:
                $this->vpc->Tattoo = min(63, max(0, $index));
                $this->vpc->EyesColor = min(7, max(0, $color));
                break;
            case EVisualSlot::RIGHT_HAND_SLOT:
                $this->vpa->WeaponRightHand = $index;
                break;
            case EVisualSlot::LEFT_HAND_SLOT:
                $this->vpa->WeaponLeftHand = $index;
                break;
            default:
                // ignore
        }
    }

    /**
     * @param int $slot
     *
     * @return array|bool
     */
    public function getSlot($slot)
    {
        switch ($slot) {
            case EVisualSlot::ARMS_SLOT:
                return [$this->vpa->ArmModel, $this->vpa->ArmColor];
            case EVisualSlot::LEGS_SLOT:
                return [$this->vpa->TrouserModel, $this->vpa->TrouserColor];
            case EVisualSlot::HANDS_SLOT:
                return [$this->vpb->HandsModel, $this->vpb->HandsColor];
            case EVisualSlot::FEET_SLOT:
                return [$this->vpb->FeetModel, $this->vpb->FeetColor];
            case EVisualSlot::CHEST_SLOT:
                return [$this->vpa->JacketModel, $this->vpa->JacketColor];
            case EVisualSlot::HEAD_SLOT:
                return [$this->vpa->HatModel, $this->vpa->HatColor];
            case EVisualSlot::FACE_SLOT:
                return [$this->vpc->Tattoo, $this->vpc->EyesColor];
            case EVisualSlot::RIGHT_HAND_SLOT:
                return [$this->vpa->WeaponRightHand];
            case EVisualSlot::LEFT_HAND_SLOT:
                return [$this->vpa->WeaponLeftHand];
            default:
                return false;
        }
    }

    /**
     * Get unique id for this setup
     *
     * @return string
     */
    public function getHash()
    {
        $tmp = $this->isFaceShot() ? 'face' : '';
        $tmp .= $this->isCropFaceShot() ? 'crop' : '';
        return sha1($tmp . $this->getBuffer());
    }

    /**
     * @return mixed
     */
    public function getBuffer()
    {
        $f = new MemStream();
        $this->serial($f);
        return $f->getBuffer();
    }

    /**
     * @param MemStream $f
     */
    public function serial(MemStream $f)
    {
        $version = 2;
        $f->serial_byte($version);

        $f->serial_byte($this->pktFormat);

        $f->serial_byte($this->race);

        $f->serial_byte($this->age);

        $this->vpa->serial($f);
        $this->vpb->serial($f);
        $this->vpc->serial($f);

        $f->serial_uint32($this->direction);
        $f->serial_uint32($this->angle);

        $b = $this->FaceShot ? 1 : 0;
        $f->serial_byte($b);

        $f->serial_uint32($this->background);

        $b = $this->useFx ? 1 : 0;
        $f->serial_byte($b);
    }
}
