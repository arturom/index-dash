<?php

namespace IndexDash\CLI;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use InvalidArgumentException;

class NonInteractiveCLI implements CLI
{
    private $client = null;

    private $opts   = null;

    public function __construct(Client $client, NonInteractiveOptions $opts)
    {
        $this->client = $client;
        $this->opts   = $opts;
    }

    public function main()
    {
        // Resolve the new index name and the alias name
        $new_index_name = sprintf(
            '%s_%s',
            $this->opts->index_name,
            date('Y_m_d_U')
        );
        $alias_name = $this->opts->index_name;

        $existing_aliases = $this->getIndexAliases($alias_name);

        // Create the new index
        $body = json_decode(stream_get_contents($this->opts->index_config), true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON decode error');
        }
        $this->client->indices()->create(
            array(
                'index' => $new_index_name,
                'body'  => $body
            )
        );

        // Copy data from the old index into the new one
        if($this->opts->copy_data) {
            throw new \RuntimeException('Not yet implemented');
        }

        // Delete the old index
        if($this->opts->delete_old) {
            foreach($existing_aliases as $index_name => $aliases) {
                $this->client->indices()->delete(
                    array(
                        'index' => $index_name
                    )
                );
            }
            // empty existing aliases array
            $existing_aliases = array();
        }

        // Move the alias from the old index to the new index
        if($this->opts->move_alias) {

            $alias_actions = array();
            foreach($existing_aliases as $index_name => $aliases) {
                $alias_actions[] = array(
                    'remove' => array(
                        'index' => $index_name,
                        'alias' => $alias_name,
                    )
                );
            }
            $alias_actions[] = array(
                'add' => array(
                    'index' => $new_index_name,
                    'alias' => $alias_name,
                )
            );
            $this->client->indices()->updateAliases(
                array(
                    'body' => array(
                        'actions' => $alias_actions
                    )
                )
            );
        }

        echo 'Created index ', $new_index_name, PHP_EOL;
    }

    /**
     * getIndexAliases
     *
     * @param string $index_name
     * @return array
     */
    public function getIndexAliases($index_name) {
        try
        {
            return $this->client->indices()->getAliases(
                array(
                    'index' => $this->opts->index_name,
                )
            );
        } catch(Missing404Exception $e)
        {
            return array();
        }
    }
}
