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
use Ryzom\Common\SPropVisualA;
use Ryzom\Common\SPropVisualB;
use Ryzom\Common\SPropVisualC;
use Ryzom\Common\TPeople;

/**
 * Ryzom renderer
 */
class CharacterFactory
{
    /** @var RaceDefaults */
    protected $raceStats;

    /**
     * @param string $path
     */
    public function loadResources($path)
    {
        $data = file_get_contents($path.'/race_defaults.txt');
        $this->raceStats = new RaceDefaults();
        $this->raceStats->load($data);
    }

    /**
     * Parse input for character data
     *
     * @param array $args
     *
     * @return \Rrs\Character
     */
    public function create(array $args)
    {
        if (empty($this->raceStats)) {
            // automatically load from Resources directory if needed
            $this->loadResources(__DIR__.'/Resources');
        }

        if (isset($args['vpa']) && isset($args['vpb']) && isset($args['vpc'])) {
            // decode vpx values into $args
            $args = $this->decodeVisualProps($args);
        }

        $args = array_merge(
            [
                'race' => 'fy',
                'gender' => 'f',
                'age' => 0,
                'dir' => 0,
                'zoom' => 'body',
                'tattoo' => 0,
                'eyes' => 0,
                'morph' => '3,3,3,3,3',
                'gabarit' => '7,7,7,7,7',
            ],
            $args
        );

        $char = new Character();

        $race = $this->normalizeRace($args['race']);
        $char->setRace($race);

        $age = max(0, min(2, $args['age']));
        $char->setAge($age);

        // allow character rotation on 45degree increments
        $dir = (int)$args['dir'];
        $char->setDirection(floor($dir / 45) * 45);
        $char->setAngle(0);

        $isPortrait = $args['zoom'] === 'portrait';
        $isHeadShot = $args['zoom'] === 'face' || $isPortrait;
        $char->setFaceShot($isHeadShot, !$isPortrait);

        // FIXME: allow to set background?
        //$bgColor = isset($args['bg']) ? $args['bg'] : false;
        $char->setBackground(0, 0, 0, 0);

        $gender = EGender::fromValue($args['gender']);
        if ($gender === false) {
            $gender = EGender::FEMALE;
        }
        $char->setGender($gender);

        // setup race default armor / haircut
        $defaults = $this->raceStats->getRaceItems($race, $gender);
        foreach ($defaults as $slot => $sheetid) {
            $index = ryzom_vs_index($slot, $sheetid);
            $char->setSlot($slot, $index);
        }

        $tattoo = (int)$args['tattoo'];
        $eyes = (int)$args['eyes'];
        $char->setSlot(EVisualSlot::FACE_SLOT, $tattoo, $eyes);

        $morph = explode(',', $args['morph']);
        $char->setMorph($morph);

        $gabarit = explode(',', $args['gabarit']);
        $char->setGabarit($gabarit);

        // armor, process haircut first, so that helmet can override it
        $keys = array(
            'hair' => EVisualSlot::HEAD_SLOT,
            'arms' => EVisualSlot::ARMS_SLOT,
            'legs' => EVisualSlot::LEGS_SLOT,
            'hands' => EVisualSlot::HANDS_SLOT,
            'feet' => EVisualSlot::FEET_SLOT,
            'chest' => EVisualSlot::CHEST_SLOT,
            'head' => EVisualSlot::HEAD_SLOT,
            //
            'handr' => EVisualSlot::RIGHT_HAND_SLOT,
            'handl' => EVisualSlot::LEFT_HAND_SLOT,
        );

        foreach ($keys as $k => $slot) {
            if (!isset($args[$k])) {
                continue;
            }

            // <sheet>/<color>
            $pairs = explode('/', $args[$k]);
            if (!isset($pairs[1])) {
                // FIXME: 1 == beige ?
                $pairs[1] = 1;
            } else {
                $pairs[1] = (int)$pairs[1];
            }

            if ($k == 'hair') {
                $index = $this->verifyHaircutSlotIndex($race, $gender, $pairs[0]);
            } else {
                $index = $this->lookupItemSlotIndex($slot, $pairs[0]);
            }

            if ($index !== false) {
                // item lookup was success
                $char->setSlot($slot, $index, $pairs[1]);
            }
        }

        return $char;
    }

    protected function normalizeRace($race)
    {
        $race = TPeople::fromString($race);
        if ($race === false) {
            $race = TPeople::FYROS;
        }
        return $race;
    }

    /**
     * Lookup sheet name visual slot index
     *
     * @param $slot
     * @param $sheetName
     *
     * @return bool|mixed
     */
    protected function lookupItemSlotIndex($slot, $sheetName)
    {
        if (stristr($sheetName, '.sitem') === false) {
            $sheetName .= '.sitem';
        }

        return ryzom_vs_index($slot, $sheetName);
    }

    /**
     * Make sure that haircut index is available for this race/gender
     *
     * @param int $race
     * @param int $gender
     * @param int $index
     *
     * @return bool
     */
    protected function verifyHaircutSlotIndex($race, $gender, $index)
    {
        $result = false;
        $sheetName = ryzom_vs_sheet(EVisualSlot::HEAD_SLOT, $index);
        if (!empty($sheetName)) {
            // 'fy_hom_'
            $prefix = sprintf('%s_ho%s_', TPeople::toString($race), EGender::toString($gender));
            // 'fy_cheveux_'
            $prefix2 = sprintf('%s_cheveux_', TPeople::toString($race));
            $pos = stripos($sheetName, $prefix);
            $pos2 = stripos($sheetName, $prefix2);
            if ($pos === 0 || $pos2 === 0) {
                // correct haircut for this race/gender
                $result = $index;
            }
        }
        return $result;
    }

    /**
     * Expand visual prop values into $args
     *
     * @param array $args
     *
     * @return array
     */
    private function decodeVisualProps(array $args)
    {
        $vpa = new SPropVisualA();
        $vpa->setValue($this->getUnsigned64bit($args['vpa']));
        $vpb = new SPropVisualB();
        $vpb->setValue($this->getUnsigned64bit($args['vpb']));
        $vpc = new SPropVisualC();
        $vpc->setValue($this->getUnsigned64bit($args['vpc']));
        unset($args['vpa'], $args['vpb'], $args['vpc']);

        $hat = ryzom_vs_sheet(EVisualSlot::HEAD_SLOT, $vpa->HatModel);
        if ($hat) {
            if (!isset($args['race'])) {
                // take race from haircut/helmet if possible
                $args['race'] = strtolower(substr($hat, 0, 2));
                if (!in_array($args['race'], array('fy', 'ma', 'tr', 'zo'))) {
                    unset($args['race']);
                }
            }

            // map any haircut to hair so that race check can be done later
            if (strstr($hat, '_hair_') !== false || strstr($hat, '_cheveux_') !== false) {
                // uses vs index
                $args['hair'] = $vpa->HatModel.'/'.$vpa->HatColor;
                unset($args['head']);
            } else {
                unset($args['hair']);
                // uses sheetid
                $args['head'] = $hat.'/'.$vpa->HatColor;
            }
        }

        if (!isset($args['race'])) {
            // fallback - unknown haircut and url does not have race set
            $args['race'] = TPeople::FYROS;
        }

        $args['gender'] = strtolower(EGender::toString($vpa->Sex));
        $args['race'] = strtolower(TPeople::toString($this->normalizeRace($args['race'])));

        // its fine if we fail items
        $s = ryzom_vs_sheet(EVisualSlot::ARMS_SLOT, $vpa->ArmModel);
        if ($s) {
            $args['arms'] = $s.'/'.$vpa->ArmColor;
        }
        $s = ryzom_vs_sheet(EVisualSlot::LEGS_SLOT, $vpa->TrouserModel);
        if ($s) {
            $args['legs'] = $s.'/'.$vpa->TrouserColor;
        }
        $s = ryzom_vs_sheet(EVisualSlot::HANDS_SLOT, $vpb->HandsModel);
        if ($s) {
            $args['hands'] = $s.'/'.$vpb->HandsColor;
        }
        $s = ryzom_vs_sheet(EVisualSlot::FEET_SLOT, $vpb->FeetModel);
        if ($s) {
            $args['feet'] = $s.'/'.$vpb->FeetColor;
        }
        $s = ryzom_vs_sheet(EVisualSlot::CHEST_SLOT, $vpa->JacketModel);
        if ($s) {
            $args['chest'] = $s.'/'.$vpa->JacketColor;
        }

        $args['tattoo'] = $vpc->Tattoo;
        $args['eyes'] = $vpc->EyesColor;

        $handr = ryzom_vs_sheet(EVisualSlot::RIGHT_HAND_SLOT, $vpa->WeaponRightHand);
        if ($handr) {
            $args['handr'] = $handr;
        }
        $handl = ryzom_vs_sheet(EVisualSlot::LEFT_HAND_SLOT, $vpa->WeaponLeftHand);
        if ($handl) {
            $args['handl'] = $handl;
        }

        $args['morph'] = join(
            ',',
            [
                $vpc->MorphTarget1,
                $vpc->MorphTarget2,
                $vpc->MorphTarget3,
                $vpc->MorphTarget4,
                $vpc->MorphTarget5,
                $vpc->MorphTarget6,
                $vpc->MorphTarget7,
                $vpc->MorphTarget8,
            ]
        );

        $args['gabarit'] = join(
            ',',
            [
                $vpc->CharacterHeight,
                $vpc->TorsoWidth,
                $vpc->ArmsWidth,
                $vpc->LegsWidth,
                $vpc->BreastSize,
            ]
        );

        return $args;
    }

    /**
     * Convert signed 64bit int to unsigned
     *
     * @param string|int $n
     *
     * @return string
     */
    private function getUnsigned64bit($n)
    {
        if ($n[0] === '-') {
            $n = bcadd(bcpow(2, 64), $n);
        }
        return $n;
    }
}
