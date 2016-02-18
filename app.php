#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use Elasticsearch\Client;
use IndexDash\CLI\NonInteractiveCLI;
use IndexDash\CLI\NonInteractiveOptions;
use Psr\Log\LogLevel;

$opts = new NonInteractiveOptions(getopt('', NonInteractiveOptions::getSupportedOptions()));
$client = new Client(array(
    'hosts' => array($opts->host)
));

$shell = new NonInteractiveCLI($client, $opts);

$shell->main();
