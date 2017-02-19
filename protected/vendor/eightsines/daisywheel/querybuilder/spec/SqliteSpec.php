<?php

/*

# insertOrIgnore

INSERT OR IGNORE INTO tablename (col1, col2) VALUES ('val1.1', 'val2.1'), ('val1.2', 'val2.2')

# insertOrReplace

INSERT OR REPLACE INTO tablename (col1, col2) VALUES ('val1.1', 'val2.1'), ('val1.2', 'val2.2')

# createTable -> asSelect

CREATE TABLE t1 AS SELECT col1, col2 FROM t2

# rename table

PRAGMA index_list('tablename'); -> show indexes
PRAGMA foreign_key_list('tablename'); -> show foreign keys

*/
