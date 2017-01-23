<?php

/*

# insertOrIgnore

## version < 9.5

WITH
qb_values (id, name, subname) AS (VALUES (1, 'n1', 's1'), (2, 'n2', 's2'), (3, 'n3', 's3'))
INSERT INTO test (id, name, subname) SELECT id, name, subname FROM qb_values WHERE NOT EXISTS (SELECT 1 FROM test WHERE id = qb_values.id);

## version >= 9.5

INSERT INTO tablename (col1, col2) VALUES ('val1', 'val2') ON CONFLICT DO NOTHING

# insertOrReplace

## version < 9.5

WITH
qb_values (id, name, subname) AS (VALUES (1, 'n1.2', 's1.2'), (2, 'n2.2', 's2.2'), (4, 'n4.1', 's4.1')),
qb_upsert AS (UPDATE test SET name = qb_values.name, subname = qb_values.subname FROM qb_values WHERE test.id = qb_values.id RETURNING test.*)
INSERT INTO test (id, name, subname) SELECT id, name, subname FROM qb_values WHERE NOT EXISTS (SELECT 1 FROM qb_upsert WHERE id = qb_values.id);

## version >= 9.5

INSERT INTO test (id, name, subname) VALUES (1, 'n1.1', 's1.1'), (2, 'n2', 's2'), (4, 'n4.1', 's4.1')
ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name, subname = EXCLUDED.subname;

# createTable -> asSelect

CREATE TABLE t1 AS SELECT col1, col2 FROM t2

*/
