<?php
namespace pulledbits\ActiveRecord\SQL\Source;

use pulledbits\ActiveRecord\Source\GeneratorGeneratorFactory;

class TableTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Table
     */
    private $object;

    protected function setUp()
    {
        $this->object = new Table();
    }

    public function testDescribe_When_DefaultState_Expect_ClassDescription()
    {
        $mockTable = \pulledbits\ActiveRecord\Test\createMockTable('MyTable', [
            'name' => [
                'primaryKey' => true,
                'auto_increment' => true,
                'required' => true
            ],
            'birthdate' => [
                'primaryKey' => true,
                'auto_increment' => false,
                'required' => true
            ],
            'address' => [
                'primaryKey' => false,
                'auto_increment' => false,
                'required' => true
            ],

            'role_id' => [
                'primaryKey' => false,
                'auto_increment' => false,
                'required' => false,
                'references' => [
                    'fk_othertable_role' => ['OtherTable', 'id']
                ]
            ],
            'role2_id' => [
                'primaryKey' => false,
                'auto_increment' => false,
                'required' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'id']
                ]
            ],
            'extra_column_id' => [
                'primaryKey' => false,
                'auto_increment' => false,
                'required' => false,
                'references' => [
                    'fk_anothertable_role' => ['AntoherTable', 'column_id']
                ]
            ],
        ]);

        $classDescription = $this->object->describe($mockTable, new GeneratorGeneratorFactory());
        $this->assertEquals(['name', 'birthdate'], $classDescription['identifier']);
        $this->assertEquals(['birthdate', 'address'], $classDescription['requiredAttributeIdentifiers']);
        $this->assertEquals([
            'FkOthertableRole' => [
                'table' => 'OtherTable',
                'where' => [
                    'id' => 'role_id'
                ],
            ],
            'FkAnothertableRole' => [
                'table' => 'AntoherTable',
                'where' => [
                    'id' => 'role2_id',
                    'column_id' => 'extra_column_id'
                ],
            ]
        ], $classDescription['references']);
    }

    public function testMakeReference_When_DefaultState_Expect_ReferenceWithConditions() {
        $expectedReference = [
            'table' => 'EntityTypeIdentifier',
            'where' => [
                'referenced_column_id' => 'local_column_id'
            ]
        ];
        $this->assertEquals($expectedReference, $this->object->makeReference('EntityTypeIdentifier', [
            'referenced_column_id' => 'local_column_id'
        ]));
    }
}