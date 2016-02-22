# index-dash

A CLI tool to hot swap Elasticsearch indexes when mappings and settings need to be updated. The tool enforces the use of index aliases.

The process consists of the following steps:
  1. Creating a new index using the mappings and settings received via STDIN. This new index name is automatically suffixed with a time stamp.
  2. Copying the data from the existing index into the new index.
  3. Deleting the old index.
  4. Moving the alias from the old index into the new one.

#### Sample usage:
```bash
$ echo '{}' | ./app.php --host='localhost:9200' --index_name='logs' --copy_data --delete_old --move_alias
```
This command instructs the script to:
  - connect to an Elasticsearch node at `localhost:9200`
  - create a new index called `logs_{current_timestamp}`
  - read the index mappings and settings from standard input
  - copy the data from any existing index called `logs` or any indexes using the `logs` alias
  - delete any existing index called `logs` or any indexes using the `logs` alias
  - remove the alias from any indexes using the `logs` alias and add the alias to the new index
