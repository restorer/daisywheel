<?php

namespace daisywheel\tests\unit\querybuilder;

use daisywheel\querybuilder\BuildHelper;

class BuildHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return void
     * @covers \daisywheel\querybuilder\BuildHelper::arg
     */
    public function testArg()
    {
        $this->assertEquals(['a'], BuildHelper::arg('a'));
        $this->assertEquals(['a'], BuildHelper::arg(['a']));
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\BuildHelper::arg
     */
    public function testArgs()
    {
        $this->assertEquals(['a', 'b'], BuildHelper::args(['a', 'b']));
        $this->assertEquals(['a', 'b'], BuildHelper::args([['a', 'b']]));
    }

    /**
     * @return void
     * @covers \daisywheel\querybuilder\BuildHelper::arg
     * @expectedException \daisywheel\querybuilder\BuildException
     */
    public function testArgsException()
    {
        BuildHelper::args([['a', 'b'], 'c']);
    }
}
