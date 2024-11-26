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
 * Class CharacterFactoryTest
 * @package Rrs
 */
class CharacterFactoryTest extends \PHPUnit_Framework_TestCase
{

    /** @var CharacterFactory */
    protected $factory;

    public function setUp()
    {
        $this->factory = new CharacterFactory();
    }

    public function testCreateDefault()
    {
        $args = [];
        $char = $this->factory->create($args);
        $this->assertEquals(TPeople::FYROS, $char->getRace());
        $this->assertEquals(EGender::FEMALE, $char->getGender());
        $this->assertEquals(0, $char->getDirection());
        $this->assertEquals([21, 0], $char->getSlot(EVisualSlot::ARMS_SLOT));
        $this->assertEquals([21, 0], $char->getSlot(EVisualSlot::LEGS_SLOT));
        $this->assertEquals([21, 0], $char->getSlot(EVisualSlot::HANDS_SLOT));
        $this->assertEquals([19, 0], $char->getSlot(EVisualSlot::FEET_SLOT));
        $this->assertEquals([20, 0], $char->getSlot(EVisualSlot::CHEST_SLOT));
        $this->assertEquals([2, 0], $char->getSlot(EVisualSlot::HEAD_SLOT));
        $this->assertEquals([0], $char->getSlot(EVisualSlot::RIGHT_HAND_SLOT));
        $this->assertEquals([0], $char->getSlot(EVisualSlot::LEFT_HAND_SLOT));
    }

    public function testCreateFromVisualProp()
    {
        $args = [
            'race' => 'ma',
            'vpa' => 2,
            'vpb' => 1 << 16,
            'vpc' => 1,
        ];
        $char = $this->factory->create($args);
        $this->assertEquals(TPeople::MATIS, $char->getRace());
        $this->assertEquals(EGender::MALE, $char->getGender());
        $this->assertEquals([1, 0], $char->getSlot(EVisualSlot::HANDS_SLOT));
        $this->assertEquals([1, 0, 0, 0, 0, 0, 0, 0], $char->getMorph());
    }

    public function testCreateCharacter()
    {
        $args = [
            'race' => 'tr',
            'gender' => 'm',
            'dir' => 91,
            'zoom' => 'head',
            'tattoo' => 1,
            'eyes' => 5,
            'morph' => '1,2,3,4,5,6,7,8', // 7
            'gabarit' => '1,5,7,10,15', // 14
            'hair' => '16/1', // tr_cheveux_long01.sitem
            //
            'arms' => 'icfahs.sitem/1',
            'legs' => 'icfalp.sitem/2',
            'hands' => 'icfamg.sitem/3',
            'feet' => 'icmahb.sitem/4',
            'chest' => 'icmalv.sitem/5',
            //
            'handr' => 'icmr1p.sitem',
            'handl' => 'ictsb.sitem',
        ];

        $char = $this->factory->create($args);
        $this->assertEquals(TPeople::TRYKER, $char->getRace());
        $this->assertEquals(EGender::MALE, $char->getGender());
        $this->assertEquals(90, $char->getDirection(), 'dir should be in 45 degree increments');
        $this->assertEquals([1, 1], $char->getSlot(EVisualSlot::ARMS_SLOT));
        $this->assertEquals([2, 2], $char->getSlot(EVisualSlot::LEGS_SLOT));
        $this->assertEquals([3, 3], $char->getSlot(EVisualSlot::HANDS_SLOT));
        $this->assertEquals([4, 4], $char->getSlot(EVisualSlot::FEET_SLOT));
        $this->assertEquals([5, 5], $char->getSlot(EVisualSlot::CHEST_SLOT));
        $this->assertEquals([16, 1], $char->getSlot(EVisualSlot::HEAD_SLOT));
        $this->assertEquals([8], $char->getSlot(EVisualSlot::RIGHT_HAND_SLOT));
        $this->assertEquals([9], $char->getSlot(EVisualSlot::LEFT_HAND_SLOT));
    }

    public function testCreateWithHelmet()
    {
        $args = [
            'hair' => '16/1', // tr_cheveux_long01.sitem
            'head' => 'icfahh.sitem/6',
        ];
        $char = $this->factory->create($args);
        $this->assertEquals([26, 6], $char->getSlot(EVisualSlot::HEAD_SLOT), 'helmet should override haircut');
    }

    public function testCreateWithRaceHelmet()
    {
        $args = [
            'race' => 'zo',
            'hair' => '16/1', // tr_cheveux_long01.sitem
            'head' => 'ma_helmet_01.sitem/6',
        ];
        $char = $this->factory->create($args);
        $this->assertEquals([15, 6], $char->getSlot(EVisualSlot::HEAD_SLOT), 'helmet should override haircut');
        $this->assertEquals(TPeople::ZORAI, $char->getRace(), 'helmet/haircut sheet should not override race');
    }

    public function testCreateWithRaceHelmetNoRace()
    {
        $args = [
            'hair' => '16/1', // tr_cheveux_long01.sitem
            'head' => 'ma_helmet_01.sitem/6',
        ];
        $char = $this->factory->create($args);
        $this->assertEquals([15, 6], $char->getSlot(EVisualSlot::HEAD_SLOT), 'helmet should override haircut');
        $this->assertEquals(TPeople::MATIS, $char->getRace(), 'race should of been set from helmet/haircut sheet');
    }

    public function testCreateWithInvalidHaircut()
    {
        $args = [
            'race' => 'fy',
            'gender' => 'f',
            'hair' => '16/1',
        ];
        $char = $this->factory->create($args);
        $this->assertEquals(
            [2, 0],
            $char->getSlot(EVisualSlot::HEAD_SLOT),
            'character should of kept default haircut (fy_cheveux_medium01)'
        );
    }

    public function testCreateWithInvalidItem()
    {
        $args = [
            'race' => 'fy',
            'gender' => 'f',
            'arms' => 'icfahg.sitem/5',
        ];
        $char = $this->factory->create($args);
        $this->assertEquals(
            [21, 0],
            $char->getSlot(EVisualSlot::ARMS_SLOT),
            'character should of kept default sleeves (igfau/0)'
        );
    }

    public function testCreateWithInvalidTattooAndEyeColor()
    {
        $args = [
            'tattoo' => 100,
            'eyes' => 100,
        ];

        $char = $this->factory->create($args);

        list($tattoo, $eyeColor) = $char->getSlot(EVisualSlot::FACE_SLOT);
        $this->assertEquals(63, $tattoo, 'tattoo value must be in 0..63 range');
        $this->assertEquals(7, $eyeColor, 'eye color value must be in 0..7 range');
    }

    public function testCreateWithNegativeTattooAndEyeColor()
    {
        $args = [
            'tattoo' => -100,
            'eyes' => -100,
        ];

        $char = $this->factory->create($args);

        list($tattoo, $eyeColor) = $char->getSlot(EVisualSlot::FACE_SLOT);
        $this->assertEquals(0, $tattoo, 'tattoo value must be in 0..63 range');
        $this->assertEquals(0, $eyeColor, 'eye color value must be in 0..7 range');
    }
}
