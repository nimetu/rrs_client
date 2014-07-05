<?php

require __DIR__.'/../vendor/autoload.php';

$config = include __DIR__.'/config.php';
$rrsServer = $config['rrs.server'];

$_GET = array(
    'race' => 'tryker',
    'gender' => 'f',
    'zoom' => 'face',
    'age' => 2,
);

$factory = new Rrs\CharacterFactory();
$char = $factory->create($_GET);

$socket = new Rrs\Socket\Socket($rrsServer);
$client3d = new \Rrs\RrsClient($socket);
$client2d = new \Rrs\DressingRoomClient($config['dressingroom.images']);

render($client2d, $char, 'render-2d.png');
render($client3d, $char, 'render-3d.png');

function render($client, $char, $output){
    $png = $client->render($char);

    if ($png !== false) {
        echo "+  OK : ({$output}), size ".strlen($png)."\n";
        file_put_contents(__DIR__.'/'.$output, $png);
    } else {
        echo "- FAIL ({$output})\n";
    }
}
