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

/**
 * RaceDefaults
 */
class RaceDefaults
{

    /** @var array */
    protected $defaults = array();

    /**
     * Unserialize
     *
     * @param string $data
     */
    public function load($data)
    {
        $lines = explode("\n", $data);
        $race = false;
        $gender = false;
        $items = false;
        foreach ($lines as $line) {
            if (preg_match('/^RACE : (\d+)$/', $line, $match)) {
                $race = (int)$match[1];
                $items = false;
            } else if (preg_match('/^GENDER : (\d+)$/', $line, $match)) {
                $gender = (int)$match[1];
                $items = false;
            } else if (preg_match('/^ITEMS :$/', $line)) {
                $items = true;
            } else if ($items && preg_match('/(\d+) : (.*)$/', $line, $match)) {
                $slot = (int)$match[1];
                $sheetName = trim($match[2]);
                if ($race !== false && $gender !== false) {
                    $this->defaults[$race][$gender]['items'][$slot] = $sheetName;
                }
            }
        }
    }

    /**
     * Serialize
     *
     * @return string
     */
    public function save()
    {
        $result = array();
        foreach ($this->defaults as $race => $genders) {
            foreach ($genders as $gender => $genderInfo) {
                $result[] = sprintf('RACE : %d', $race);
                $result[] = sprintf('GENDER : %d', $gender);
                $result[] = sprintf('ITEMS :');
                foreach ($genderInfo['items'] as $slot => $sheetName) {
                    $result[] = sprintf('%d : %s', $slot, $sheetName);
                }
            }
        }
        return join("\n", $result);
    }

    /**
     * @param $race
     * @param $gender
     *
     * @return mixed
     * @throws \RuntimeException
     */
    public function getRaceItems($race, $gender)
    {
        if (!isset($this->defaults[$race][$gender])) {
            throw new \RuntimeException("Unknown race ($race) or gender ($gender)");
        }
        return $this->defaults[$race][$gender]['items'];
    }

    /**
     * Clear current race/gender info
     */
    public function clear()
    {
        $this->defaults = array();
    }

    /**
     * Set default items for race/gender
     *
     * @param int $race
     * @param int $gender
     * @param array $items
     */
    public function setRaceItems($race, $gender, array $items)
    {
        $this->defaults[$race][$gender]['items'] = $items;
    }
}
