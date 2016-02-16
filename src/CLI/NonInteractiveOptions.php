<?php

namespace IndexDash\CLI;

use InvalidArgumentException;
use RuntimeException;

class NonInteractiveOptions implements Options
{
    public $host          = 'localhost:9200';
    public $index_name    = null;
    public $index_config  = STDIN;
    public $move_alias    = false;
    public $copy_data     = false;
    public $delete_old    = false;

    /**
     * __construct
     *
     * @param array $opts
     */
    public function __construct(array $opts)
    {
        // Host option
        if(isset($opts['host'])) {
            $this->host = $opts['host'];
        }
        if(empty($this->host)) {
            throw new InvalidArgumentException('"host" parameter cannot be empty');
        }

        // Index name option
        if(empty($opts['index_name'])) {
            throw new InvalidArgumentException('Missing "index_name" paramater');
        }
        $this->index_name = $opts['index_name'];

        // Index configuration option
        if(isset($opts['index_config'])) {
            $this->config_stream = @fopen($opts['index_config'], 'r');
            if($this->config_stream === false) {
                throw new RuntimeException(sprintf('Failed to open stream: "%s"', $opts['index_config']));
            }
        }

        // Move alias option
        if(isset($opts['move_alias'])) {
            $this->move_alias = true;
        }

        // Copy data option
        if(isset($opts['copy_data'])) {
            $this->copy_data = true;
        }

        // Delete old index
        if(isset($opts['delete_old'])) {
            $this->delete_old = true;
        }
    }

    /**
     * getSupportedOptions
     * @return array
     *
     */
    public static function getSupportedOptions()
    {
        return array(
            'host:',
            'index_name:',
            'index_config:',
            'move_alias',
            'copy_data',
            'delete_old',
        );
    }
}
