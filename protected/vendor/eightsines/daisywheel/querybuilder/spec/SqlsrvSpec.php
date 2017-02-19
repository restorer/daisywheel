<?php

/*

# insertOrIgnore

## version < 2008

попробовать подход с WITH, как в pgsql?

BEGIN
INSERT INTO tablename (col1, col2) SELECT 'val1.1', 'val2.1' WHERE NOT EXISTS (SELECT 1 FROM tablename WHERE col1 = 'val1.1')
INSERT INTO tablename (col1, col2) SELECT 'val1.2', 'val2.2' WHERE NOT EXISTS (SELECT 1 FROM tablename WHERE col1 = 'val1.2')
...
COMMIT

## version >= 2008

MERGE tablename WITH (HOLDLOCK) AS _qbtarget
USING (VALUES ('val1.1', 'val2.1'), ('val1.2', 'val2.2')) AS _qbsource (col1, col2) ON _qbtarget.col1 = _qbsource.col1
WHEN NOT MATCHED THEN INSERT (col1, vol2) VALUES (_qbsource.col1, _qbsource.col2)

# insertOrReplace

## version < 2008

попробовать подход с WITH, как в pgsql?

BEGIN
UPDATE tablename WITH (serializable) SET col2 = 'val2.1' WHERE col1 = 'val1.1'
IF @@ROWCOUNT = 0 INSERT INTO tablename (col1, col2) VALUES ('val1.1', 'val2.1')
UPDATE tablename WITH (serializable) SET col2 = 'val2.2' WHERE col1 = 'val1.2'
IF @@ROWCOUNT = 0 INSERT INTO tablename (col1, col2) VALUES ('val1.2', 'val2.2')
...
COMMIT

## version >= 2008

MERGE tablename WITH (HOLDLOCK) AS _qbtarget
USING (VALUES ('val1.1', 'val2.1'), ('val1.2', 'val2.2')) AS _qusource (col1, col2) ON _qbtarget.col1 = _qbsource.col1
WHEN MATCHED THEN UPDATE SET col2 = _qbsource.col2 WHERE col1 = _qbsource.col1
WHEN NOT MATCHED THEN INSERT (col1, vol2) VALUES (_qbsource.col1, _qbsource.col2)

# createTable -> asSelect

SELECT col1, col2 INTO t1 FROM t2

# rename table

EXEC sp_rename 'old_table_name', 'new_table_name'

*/
