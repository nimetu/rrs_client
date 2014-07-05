<?php

require __DIR__ . '/../vendor/autoload.php';

$config = include __DIR__.'/config.php';

$dataPath = $config['ryzomDataPath'];

$dataFiles = array(
	'leveldesign.bnp',
	'race_stats.packed_sheets',
);

$failed = false;
foreach($dataFiles as $file){
	$fpath = $dataPath.'/'.$file;
	if (!file_exists($fpath)) {
		echo "- file not found ($fpath)\n";
		$failed = true;
	}
}
if ($failed) {
	exit(1);
}

$updater = new Rrs\Export\RaceDefaults($dataPath);
$updater->run();

