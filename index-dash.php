#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use IndexDash\CLI\NonInteractiveCLI;
use IndexDash\CLI\NonInteractiveOptions;

use Elasticsearch\Client;
use Psr\Log\LogLevel;

// CLI Options
$opts = NonInteractiveOptions::createFromGetOpt();

// Set timezone
if ($opts->timezone) {
    if (date_default_timezone_set($opts->timezone) === false) {
        throw new Exception();
    }
}

// Elasticsearch client
$client = new Client(array(
    'hosts' => array($opts->host)
));

// Main app
$shell = new NonInteractiveCLI($client, $opts);
$shell->main();
