<?php

namespace IndexDash\CLI;

class InteractiveCLI implements CLI
{

    public function __construct()
    {
    }

    public function main()
    {
        $alias_name = $this->prompString('Enter the main index name');
        $existing_index_name = $this->client->resolveExistingIndexName($alias_name);
        $is_alias = $alias_name === $existing_index_name;
    }

    public function promptYesNo($message)
    {
        while(true) {
            echo $message, ' (y/n): >';
            $response = strtolower(fread(STDIN));
            if($response === 'y') {
                return true;
            }
            if($response === 'n') {
                return false;
            }
        }
    }

    public function prompString($message)
    {
        echo $message, ' : >';
        return fread(STDIN);
    }
}
