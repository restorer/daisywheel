<?php

use daisywheel\core\Context;
use daisywheel\core\Config;

class BuilderTest extends PHPUnit_Framework_TestCase
{
    protected function createContext($driver, $version)
    {
        return new Context(new Config([
            'components' => [
                'db' => [
                    'dsn' => "{$driver}:mock=daisywheel\\tests\\mock\\db\\MockPdo",
                    'username' => 'test',
                    'password' => 'test',
                    'driverOptions' => [
                        'sqlServerVersion' => $version,
                    ],
                ],
            ],
        ]));
    }

    protected function readTestCase($fileName)
    {
        $xml = PHPUnit_Util_XML::loadFile($fileName);

        $result = [
            'items' => [],
        ];

        foreach ($xml->firstChild->childNodes as $node) {
            if (!($node instanceof DOMElement)) {
                continue;
            }

            if ($node->nodeName == 'builder') {
                $result['builder'] = trim($node->nodeValue);
            } else {
                $result['items'][] = [
                    'driver' => trim($node->getAttribute('driver')),
                    'version' => trim($node->getAttribute('version')),
                    'compare' => trim($node->nodeValue),
                ];
            }
        }

        return $result;
    }

    public function executeTestCase($fileName)
    {
        $case = $this->readTestCase($fileName);

        foreach ($case['items'] as $item) {
            $context = $this->createContext($item['driver'], $item['version']);
            $sql = eval('return $context->db->builder(function($b) { ' . $case['builder'] . ' })->build();');

            if (is_array($sql)) {
                $sql = join(";\n", $sql);
            }

            $this->assertEquals($sql . ';', $item['compare']);
        }
    }

    public function testSelect()
    {
        $this->executeTestCase(__DIR__ . '/test-select.xml');
    }

    public function testInsert()
    {
        $this->executeTestCase(__DIR__ . '/test-insert.xml');
    }

    public function testUpdate()
    {
        $this->executeTestCase(__DIR__ . '/test-update.xml');
    }

    public function testDelete()
    {
        $this->executeTestCase(__DIR__ . '/test-delete.xml');
    }

    public function testCreateTable()
    {
        $this->executeTestCase(__DIR__ . '/test-create-table.xml');
    }

    public function testTruncateTable()
    {
        $this->executeTestCase(__DIR__ . '/test-truncate-table.xml');
    }

    public function testDropTable()
    {
        $this->executeTestCase(__DIR__ . '/test-drop-table.xml');
    }
}
