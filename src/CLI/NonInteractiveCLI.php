<?php

namespace IndexDash\CLI;

use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Guzzle\Http\Exception\ServerErrorResponseException;

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
        // Ping the Elasticsearch node
        $this->client->ping();

        // Resolve the new index name and the alias name
        $new_index_name = sprintf(
            '%s_%s',
            $this->opts->index_name,
            date('Y_m_d_U')
        );

        $alias_name = $this->opts->index_name;

        $existing_aliases = $this->getIndexAliases($alias_name);

        // Create the new index
        $this->createIndex($this->client, $new_index_name, $this->opts->index_config);

        // Copy data from the old index into the new one
        if($this->opts->copy_data) {
            $this->copyData($this->client, $this->opts->batch_size);
        }

        // Delete the old index
        if($this->opts->delete_old) {
            $this->deleteOldIndices($this->client, $existing_aliases);
            // empty the existing aliases array
            $existing_aliases = array();
        }

        // Move the alias from the old indexes to the new index
        if($this->opts->move_alias) {
            $this->moveAlias($this->client, $existing_aliases, $new_index_name, $alias_name);
        }

        echo 'Created index ', $new_index_name, PHP_EOL;
    }

    /**
     * getIndexAliases
     *
     * @param string $index_name
     * @return array
     */
    public function getIndexAliases($index_name)
    {
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
        } catch(ServerErrorResponseException $e)
        {
            return array();
        }
    }

    private function createIndex($client, $new_index_name, $config_stream)
    {
        $body = json_decode(stream_get_contents($config_stream), true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('JSON decode error');
        }
        return $client->indices()->create(
            array(
                'index' => $new_index_name,
                'body'  => $body
            )
        );
    }

    private function copyData(Client $client, $source_index, $destination_index, $batch_size)
    {
        $search_params = array(
            "search_type" => "scan",    // use search_type=scan
            "scroll"      => "30s",     // how long between scroll requests. should be small!
            "size"        => 50,        // how many results *per shard* you want back
            "index"       => $source_index,
            "body" => array(
                "query" => array(
                    "match_all" => array()
                )
            )
        );

        $search_responses = new SearchResponseIterator($client, $search_params);

        foreach($search_responses as $search_response) {

            $index_params = array();
            $index_params['index'] = $destination_index;

            foreach($search_response['hits']['hits'] as $hit) {
                $index_params['body'][] = array(
                    'type' => $hit['_type'],
                    'id'   => $hit['_id']
                );
                $index_params['body'][] = $hit['source'];
            }

            $client->bulk($index_params);
        }
    }

    private function deleteOldIndices(Client $client, array $existing_aliases)
    {
        foreach($existing_aliases as $index_name => $aliases) {
            $client->indices()->delete(
                array(
                    'index' => $index_name
                )
            );
        }
    }

    private function moveAlias(Client $client, $existing_aliases, $new_index_name, $alias_name)
    {
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
        return $client->indices()->updateAliases(
            array(
                'body' => array(
                    'actions' => $alias_actions
                )
            )
        );
    }
}
