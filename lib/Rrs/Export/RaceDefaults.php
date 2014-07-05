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

namespace Rrs\Export;

use Nel\Misc\BnpFile;
use Nel\Misc\SheetId;
use Rrs\RaceDefaults as RaceDefaultsModel;
use Ryzom\Common\EVisualSlot;
use Ryzom\Sheets\Client\RaceStatsSheet;
use Ryzom\Sheets\PackedSheetsLoader;
use Ryzom\Sheets\SheetsManager;

/**
 * Update Resources datafiles
 */
class RaceDefaults
{
    protected $resourcePath;
    protected $ryzomDataPath;

    /** @var SheetId */
    protected $sheetIds;

    /** @var SheetsManager */
    protected $sheets;

    /**
     * @param string $ryzomDataPath
     */
    public function __construct($ryzomDataPath)
    {
        $this->resourcePath = __DIR__.'/../Resources';

        $this->ryzomDataPath = $ryzomDataPath;
    }

    /**
     * Directory to save generated files
     *
     * @param string $path
     */
    public function setResourcePath($path)
    {
        $this->resourcePath = $path;
    }

    /**
     * .bnp and .packed_sheets directory
     *
     * @param string $path
     */
    public function setRyzomDataPath($path)
    {
        $this->ryzomDataPath = $path;
    }

    public function run()
    {
        echo "+ sheet_id.bin\n";
        $this->loadSheets();

        echo "+ race_defaults.serial\n";
        $data = $this->genRaceStats();
        file_put_contents($this->resourcePath.'/race_defaults.txt', $data);
    }

    /**
     * Load sheet_id.bin from leveldesign.bnp
     * Initialize SheetId and SheetsManager
     */
    private function loadSheets()
    {
        $bnpLeveldesign = new BnpFile($this->ryzomDataPath.'/leveldesign.bnp');
        $buffer = $bnpLeveldesign->readFile('sheet_id.bin');

        $this->sheetIds = new SheetId();
        $this->sheetIds->load($buffer);

        $psLoader = new PackedSheetsLoader($this->ryzomDataPath);
        $this->sheets = new SheetsManager($this->sheetIds, $psLoader);
    }

    /**
     * Parse <race>.race_stats packed sheets
     *
     * @return string
     */
    private function genRaceStats()
    {
        $raceStats = new RaceDefaultsModel();
        $raceStats->clear();

        $races = array('fyros', 'matis', 'tryker', 'zorai');
        foreach ($races as $race) {
            $sheet = $race.'.race_stats';
            echo "-- $sheet\n";
            $id = $this->sheetIds->getSheetId($sheet);
            /** @var $stats RaceStatsSheet */
            $stats = $this->sheets->findById($id);
            foreach ($stats->GenderInfos as $gender => $genderInfos) {
                $result = array();
                foreach ($genderInfos->Items as $sheetName) {
                    $slot = $this->findSheetSlot($sheetName);
                    $result[$slot] = $sheetName;
                }
                $raceStats->setRaceItems($stats->People, $gender, $result);
            }
        }

        return $raceStats->save();
    }

    /**
     * Find which slot does sheet belong to
     *
     * @param string $sheetName
     *
     * @return int|bool
     */
    private function findSheetSlot($sheetName)
    {
        $slots = array(
            EVisualSlot::HIDDEN_SLOT,
            EVisualSlot::CHEST_SLOT,
            EVisualSlot::LEGS_SLOT,
            EVisualSlot::HEAD_SLOT,
            EVisualSlot::ARMS_SLOT,
            EVisualSlot::FACE_SLOT,
            EVisualSlot::HANDS_SLOT,
            EVisualSlot::FEET_SLOT,
            EVisualSlot::RIGHT_HAND_SLOT,
            EVisualSlot::LEFT_HAND_SLOT,
        );

        foreach ($slots as $slot) {
            if (ryzom_vs_index($slot, $sheetName)) {
                return $slot;
            }
        }
        return false;
    }
}
