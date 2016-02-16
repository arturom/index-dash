# index-dash

A CLI utility to replace Elasticsearch indexes with a new one.

The name of the new index is composed using the name provided and a time stamp. The index name provided is intended to be used as an alias.
The tool can automatically transfer the alias from the previous index into the new one
When the new mapping is compatible with the old mapping, it is possible to copy the data from the old index into the new index.


#### Usage example:
Create an index intended to be used as `/logs` but actually named with a date pattern `logs_Y_m_d_U`
  - reading the index mappings and settings from standard input
  - deleting any other index called `logs`
  - transfering the alias fromt the old `logs` index into the new one.

```bash
echo '{}' | ./app.php --host='localhost:9200' --index_name='logs' --delete_old --copy_data --move_alias
```
