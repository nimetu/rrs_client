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

use Ryzom\Common\EVisualSlot;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2012-10-27 at 21:22:20.
 */
class CharacterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Character */
    protected $character;

    /**
     * @covers Character::__construct
     * @covers Character::getHash
     * @covers Character::getBuffer
     * @covers Character::serial
     */
    public function setUp()
    {
        $empty = '748947628835c51f73843ac2b81f051794bc949a';
        $this->character = new Character();
        $this->assertEquals($empty, $this->character->getHash());
    }

    /**
     * @covers Character::setVpa
     */
    public function testSetVpa()
    {
        // properties are all set to 1
        $vpa = '2310911766417117699';
        $this->character->setVpa($vpa);

        $this->assertEquals(1, $this->character->getGender());
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::CHEST_SLOT));
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::LEGS_SLOT));
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::ARMS_SLOT));
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::HEAD_SLOT));
        $this->assertEquals([1], $this->character->getSlot(EVisualSlot::RIGHT_HAND_SLOT));
        $this->assertEquals([1], $this->character->getSlot(EVisualSlot::LEFT_HAND_SLOT));
    }

    /**
     * @covers Character::getVpa
     */
    public function testGetVpa()
    {
        $this->assertEquals(0, $this->character->getVpa());

        // properties are all set to 1
        $vpa = '2310911766417117699';
        $this->character->setGender(1);
        $this->character->setSlot(EVisualSlot::CHEST_SLOT, 1, 1);
        $this->character->setSlot(EVisualSlot::LEGS_SLOT, 1, 1);
        $this->character->setSlot(EVisualSlot::ARMS_SLOT, 1, 1);
        $this->character->setSlot(EVisualSlot::HEAD_SLOT, 1, 1);
        $this->character->setSlot(EVisualSlot::RIGHT_HAND_SLOT, 1);
        $this->character->setSlot(EVisualSlot::LEFT_HAND_SLOT, 1);

        $this->assertEquals($vpa, $this->character->getVpa());
    }

    /**
     * @covers Character::setVpb
     */
    public function testSetVpb()
    {
        // properties are all set to 1
        $vpb = '18829438681089';

        $this->character->setVpb($vpb);

        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::HANDS_SLOT));
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::FEET_SLOT));
    }

    /**
     * @covers Character::getVpb
     */
    public function testGetVpb()
    {
        $this->assertEquals(0, $this->character->getVpb());

        $this->character->setSlot(EVisualSlot::HANDS_SLOT, 1, 1);
        $this->character->setSlot(EVisualSlot::FEET_SLOT, 1, 1);

        // Name=0, HandsModel=1, HandsColor=1, FeetModel=1, FeetColor=1, RTrail=0, LTrail=0
        $vpb = '137741008896';
        $this->assertEquals($vpb, $this->character->getVpb());
    }

    /**
     * @covers Character::setVpc
     */
    public function testSetVpc()
    {
        // properties are all set to 1
        $vpc = '1200958908699209';

        $this->character->setVpc($vpc);

        $this->assertEquals([1,1,1,1,1,1,1,1], $this->character->getMorph());
        $this->assertEquals([1,1], $this->character->getSlot(EVisualSlot::FACE_SLOT));
        $this->assertEquals([1,1,1,1,1], $this->character->getGabarit());
    }

    /**
     * @covers Character::getVpc
     */
    public function testGetVpc()
    {
        $this->assertEquals(0, $this->character->getVpc());

        $this->character->setMorph([1,1,1,1,1,1,1,1]);
        $this->character->setSlot(EVisualSlot::FACE_SLOT, 1, 1);
        $this->character->setGabarit([1,1,1,1,1]);

        // properties are all set to 1
        $vpc = '1200958908699209';
        $this->assertEquals($vpc, $this->character->getVpc());
    }

    /**
     * @covers Character::setBackground
     */
    public function testSetBackground()
    {
        $expected = '078a91681c1f4de06dc49a901caabc0f53c80b69';
        $this->character->setBackground(1, 1, 1, 1);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setFaceShot
     */
    public function testSetFaceShot()
    {
        $expected = 'ec9c3cc08494e30f62f07db1130057181d4a17af';
        $this->character->setFaceShot(true);
        $this->assertEquals($expected, $this->character->getHash());

        $expected = '748947628835c51f73843ac2b81f051794bc949a';
        $this->character->setFaceShot(false);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setRace
     */
    public function testSetRace()
    {
        $expected = 'dd68d50d547106b8e1d6995e644f03afc2d94928';
        $this->character->setRace(1);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setGender
     */
    public function testSetGender()
    {
        $expected = 'cd282775900ceeb29b6db9ae2b324b8315edf8ac';
        $this->character->setGender(1);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setAge
     */
    public function testSetAge()
    {
        $expected = '9f24f10016fbd22306ce6ccd9aa2dc571ecb1459';
        $this->character->setAge(1);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setDirection
     */
    public function testSetDirection()
    {
        $expected = '854a9e295537841d8a6db43d258c18646d003148';
        $this->character->setDirection(123);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setAngle
     */
    public function testSetAngle()
    {
        $expected = '1d24b183bb0ff13e29ca19ef1d80c8e61bb9f7f5';
        $this->character->setAngle(123);
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setMorph
     */
    public function testSetMorph()
    {
        $expected = '1a91d813bf13b0995843dfc7d433d30cc7e3a3c1';
        $this->character->setMorph(array(0, 1, 2, 3, 4, 5, 6, 7));
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setGabarit
     */
    public function testSetGabarit()
    {
        $expected = 'be4e174c5fa2e3baa3d9500e855b9090ab868de9';
        $this->character->setGabarit(array(1, 2, 3, 4, 5));
        $this->assertEquals($expected, $this->character->getHash());
    }

    /**
     * @covers Character::setSlot
     */
    public function testSetSlot()
    {
        $expected = '03bf60bad9e50d4a7b064860084746d2055de6a7';
        $this->character->setSlot(EVisualSlot::ARMS_SLOT, 1, 1);
        $this->assertEquals($expected, $this->character->getHash());
    }

}
