<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\QueryBuilder;
use daisywheel\tests\unit\querybuilder\mock\MockBuildSpec;

class BuilderDataTypesTest extends \PHPUnit_Framework_TestCase
{
    /** @var QueryBuilder */
    protected $builder;

    /**
     * @see \PHPUnit_Framework_TestCase::__construct()
     * @inheritdoc
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->builder = new QueryBuilder(new MockBuildSpec());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::primaryKey
     */
    public function testPrimaryKey()
    {
        $this->assertEquals(
            '[id] INT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
            $this->builder->col('id')->primaryKey()->buildPart()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::bigPrimaryKey
     */
    public function testBigPrimaryKey()
    {
        $this->assertEquals(
            '[id] BIGINT NOT NULL IDENTITY(1, 1) PRIMARY KEY',
            $this->builder->col('id')->bigPrimaryKey()->buildPart()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::tinyInt
     */
    public function testTinyInt()
    {
        $this->assertEquals('[id] TINYINT', $this->builder->col('id')->tinyInt()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::smallInt
     */
    public function testSmallInt()
    {
        $this->assertEquals('[id] SMALLINT', $this->builder->col('id')->smallInt()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::int
     */
    public function testInt()
    {
        $this->assertEquals('[id] INT', $this->builder->col('id')->int()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::bigInt
     */
    public function testBigInt()
    {
        $this->assertEquals('[id] BIGINT', $this->builder->col('id')->bigInt()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::decimal
     */
    public function testDecimal()
    {
        $this->assertEquals('[id] DECIMAL(1, 2)', $this->builder->col('id')->decimal(1, 2)->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::float
     */
    public function testFloat()
    {
        $this->assertEquals('[id] FLOAT(24)', $this->builder->col('id')->float()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::double
     */
    public function testDouble()
    {
        $this->assertEquals('[id] FLOAT(53)', $this->builder->col('id')->double()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::date
     */
    public function testDate()
    {
        $this->assertEquals('[id] DATE', $this->builder->col('id')->date()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::time
     */
    public function testTime()
    {
        $this->assertEquals('[id] TIME', $this->builder->col('id')->time()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::dateTime
     */
    public function testDateTime()
    {
        $this->assertEquals('[id] DATETIME', $this->builder->col('id')->dateTime()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::char
     */
    public function testChar()
    {
        $this->assertEquals('[id] NCHAR(255)', $this->builder->col('id')->char(255)->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::varChar
     */
    public function testVarChar()
    {
        $this->assertEquals('[id] NVARCHAR(255)', $this->builder->col('id')->varChar(255)->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::text
     */
    public function testText()
    {
        $this->assertEquals('[id] NVARCHAR(MAX)', $this->builder->col('id')->text()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::mediumText
     */
    public function testMediumText()
    {
        $this->assertEquals('[id] MEDIUMTEXT', $this->builder->col('id')->mediumText()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::longText
     */
    public function testLongText()
    {
        $this->assertEquals('[id] LONGTEXT', $this->builder->col('id')->longText()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::blob
     */
    public function testBlob()
    {
        $this->assertEquals('[id] VARBINARY(MAX)', $this->builder->col('id')->blob()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::mediumBlob
     */
    public function testMediumBlob()
    {
        $this->assertEquals('[id] MEDIUMBLOB', $this->builder->col('id')->mediumBlob()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\expr\ColumnExpr::longBlob
     */
    public function testLongBlob()
    {
        $this->assertEquals('[id] LONGBLOB', $this->builder->col('id')->longBlob()->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\parts\DataTypePart::notNull
     */
    public function testNotNull()
    {
        $this->assertEquals('[id] INT NOT NULL', $this->builder->col('id')->int()->notNull()->buildPart());
        $this->assertEquals('[id] INT', $this->builder->col('id')->int()->notNull(false)->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\parts\DataTypePart::default_
     */
    public function testDefault()
    {
        $this->assertEquals("[id] INT DEFAULT '0'", $this->builder->col('id')->int()->default_(0)->buildPart());
        $this->assertEquals('[id] INT', $this->builder->col('id')->int()->default_(null)->buildPart());
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\parts\DataTypePart::notNull
     * @covers \daisywheel\querybuilder\ast\parts\DataTypePart::default_
     */
    public function testNotNullWithDefault()
    {
        $this->assertEquals(
            "[id] INT NOT NULL DEFAULT '0'",
            $this->builder->col('id')->int()->notNull()->default_(0)->buildPart()
        );
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\ast\parts\DataTypePart::__call
     */
    public function testMagicCall()
    {
        $this->assertEquals("[id] INT DEFAULT '0'", $this->builder->col('id')->int()->default(0)->buildPart());
    }
}
