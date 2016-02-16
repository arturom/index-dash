#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use IndexDash\CLI\NonInteractiveOptions;
use Elasticsearch\Client;
use Psr\Log\LogLevel;

try {
    $opts = new NonInteractiveOptions(getopt('', NonInteractiveOptions::getSupportedOptions()));
    $client = new Client(
        array(
            'hosts'    => array($opts->host)
        )
    );
    $shell = new IndexDash\CLI\NonInteractiveCLI($client, $opts);
    $shell->main();
} catch (Exception $e) {
    echo get_class($e), ': ', $e->getMessage(),
        ' thrown at ', $e->getFile(), ':', $e->getLine(), PHP_EOL,
        $e->getTraceAsString(), PHP_EOL;
    exit(1);
}
