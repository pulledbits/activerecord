<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 25-1-17
 * Time: 15:50
 */

namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\SQL\MySQL\Table;
use function pulledbits\ActiveRecord\Test\createColumnResult;
use function pulledbits\ActiveRecord\Test\createConstraintResult;
use function pulledbits\ActiveRecord\Test\createIndexResult;
use function pulledbits\ActiveRecord\Test\createMockPDOCallback;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createMockPDOStatementProcedure;

class RecordTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Record
     */
    private $object;

    protected function setUp()
    {
        $this->pdo = createMockPDOCallback();
        $this->pdo->callback(function(string $query) {
            switch ($query) {
                case 'SHOW FULL TABLES IN MySchema':
                    return createMockPDOStatement([]);

                case 'SHOW INDEX FROM MySchema.MyTable':
                    return createMockPDOStatement([
                        createIndexResult('MyTable', CONSTRAINT_KEY_PRIMARY, 'number')
                    ]);

                case 'SHOW FULL COLUMNS IN MySchema.MyTable':
                    return createMockPDOStatement([
                        createColumnResult('number', 'INT', true),
                        createColumnResult('role_id', 'INT', true),
                        createColumnResult('pole_id', 'INT', true)
                    ]);

                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable\' */ WHERE k.table_name = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([
                        createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id')
                    ]);


                case 'SHOW INDEX FROM MySchema.MyTable2':
                    return createMockPDOStatement([
                        createIndexResult('MyTable2', CONSTRAINT_KEY_PRIMARY, 'number')
                    ]);

                case 'SHOW FULL COLUMNS IN MySchema.MyTable2':
                    return createMockPDOStatement([
                        createColumnResult('number', 'INT', true),
                        createColumnResult('name', 'INT', false),
                        createColumnResult('role_id', 'INT', true),
                        createColumnResult('role2_id', 'INT', true, true),
                        createColumnResult('pole_id', 'INT', true)
                    ]);

                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable2\' */ WHERE k.table_name = \'MyTable2\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable2\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable2\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([
                        createConstraintResult('fk_othertable_role', 'role_id', 'OtherTable', 'id'),
                        createConstraintResult('fk_othertable_role', 'role2_id', 'OtherTable', 'id2')
                    ]);
            }
        });
        $connection = new Connection($this->pdo);
        $this->schema = $connection->schema('MySchema');
        $this->object = new Record(new Table($this->schema, 'MyTable'));
        $this->object->contains([
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
    }

    public function test__get_When_ExistingProperty_Expect_Value()
    {
        $value = $this->object->number;
        $this->assertEquals('1', $value);
    }

    public function test__get_When_NotExistingProperty_Expect_Null()
    {
        $value = $this->object->doesnotexist;
        $this->assertNull($value);
    }

    public function test__set_When_ExistingProperty_Expect_ValueChanged()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'UPDATE MySchema.MyTable SET number = ' . $matchedParameters[0] . ' WHERE number = ' . $matchedParameters[1]:
                    return createMockPDOStatement(1, $matchedParameters, [
                        '2',
                        '1'
                    ]);
            }
        });

        $this->assertEquals('1', $this->object->number);
        $this->object->__set('number', '2');
        $this->assertEquals('2', $this->object->number);
    }

    public function test__set_When_NonExistingProperty_Expect_NoQueryValueUnchanged()
    {
        $this->object->contains(['numbero' => '1']);
        $this->assertEquals('1', $this->object->numbero);
        $this->object->__set('numbero', '2');
        $this->assertEquals('1', $this->object->numbero);
    }


    public function test__set_When_MissingRequiredProperty_Expect_NoChanges()
    {
        $object = new Record(new Table($this->schema, 'MyTable2'));
        $object->contains([
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
        $this->assertEquals('1', $object->number);
        $object->__set('number', '2');
        $this->assertEquals('1', $object->number);
    }

    public function test__set_When_MissingRequiredPropertyIsSet_Expect_Changes()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'UPDATE MySchema.MyTable2 SET name = ' . $matchedParameters[0] . ' WHERE number = ' . $matchedParameters[1]:
                    return createMockPDOStatement(1, $matchedParameters, [
                        '2',
                        '1'
                    ]);
            }
        });

        $object = new Record(new Table($this->schema, 'MyTable2'));
        $object->contains([
            'number' => '1',
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
        $this->assertNull($object->name);
        $object->__set('name', '2');
        $this->assertEquals('2', $object->name);
    }

    public function test__set_When_NulledRequiredProperty_Expect_NoChanges()
    {
        $object = new Record(new Table($this->schema, 'MyTable2'));
        $object->contains([
            'number' => '1',
            'name' => null,
            'role_id' => '33',
            'pole_id' => '3654',
        ]);
        $this->assertEquals('1', $object->number);
        $object->__set('number', '2');
        $this->assertEquals('1', $object->number);
    }

    public function testDelete_When_ExistingProperty_Expect_Value()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'DELETE FROM MySchema.MyTable WHERE number = ' . $matchedParameters[0]:
                    return createMockPDOStatement(1, $matchedParameters, [
                        '1'
                    ]);
            }
        });

        $this->assertEquals(1, $this->object->delete());
    }

    public function testCreate_When_DefaultState_Expect_AtLeastOneCreatedRecord()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'INSERT INTO MySchema.MyTable (number, role_id, pole_id) VALUES (' . $matchedParameters[0] . ', ' . $matchedParameters[1] . ', ' . $matchedParameters[2] . ')':
                    return createMockPDOStatement(1, $matchedParameters, [
                        '1',
                        '33',
                        '3654',
                    ]);
            }
        });

        $this->assertEquals(1, $this->object->create());
    }

    public function testCreate_When_RequiredValuesMissing_Expect_NoRecordsCreatedExpectError()
    {
        $object = new Record(new Table($this->schema, 'MyTable2'));
        $object->contains([]);

        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Required values are missing/');
        $this->assertEquals(0, $object->create());

        $object->contains(['name' => 'Test']);
        $this->assertEquals(1, $object->create());
    }

    public function test__call_When_CustomMethodCalled_Expect_ValueFromCustomMethod()
    {
        $this->object->bind('customMethod', function($a, $b, $c) {
            if (get_class($this) === Record::class) {
                return 'custom' . $a . $b . $c;
            }
        });
        $this->assertEquals('customabc', $this->object->__call('customMethod', ['a', 'b', 'c']));
    }

    public function test__call_When_CustomMethodWrappingProcedureCalled_Expect_ProcedureToBeCalledThroughEntityType()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'CALL MySchema.customProcedure(' . $matchedParameters[0] . ', ' . $matchedParameters[1] . ', ' . $matchedParameters[2] . ')':
                    return createMockPDOStatementProcedure();
            }
        });
        $this->object->bind('customMethod', function($a, $b, $c) {
            $this->table->call('customProcedure', [$a, $b, $c]);
        });
        $this->assertNull($this->object->__call('customMethod', ['a', 'b', 'c']));
    }

    public function test__call_When_ExistingReferenceFetchByCall_Expect_Value()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case "SHOW INDEX FROM MySchema.OtherTable":
                case 'SHOW FULL COLUMNS IN MySchema.OtherTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'OtherTable\' */ WHERE k.table_name = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'OtherTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT * FROM MySchema.OtherTable WHERE id = ' . $matchedParameters[0]:
                    return createMockPDOStatement([
                        ['id' => '356']
                    ], $matchedParameters, [
                        '33'
                    ]);
            }
        });

        $records = $this->object->__call('fetchByFkOthertableRole', []);
        $this->assertEquals('356', $records[0]->id);
    }

    public function test__call_When_ExistingReferenceFetchByCallWithAdditionalConditions_Expect_Value()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case "SHOW INDEX FROM MySchema.OtherTable":
                case 'SHOW FULL COLUMNS IN MySchema.OtherTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'OtherTable\' */ WHERE k.table_name = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'OtherTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'SELECT * FROM MySchema.OtherTable WHERE extra = ' . $matchedParameters[0] . ' AND id = ' . $matchedParameters[1] . '':
                    return createMockPDOStatement([
                        ['id' => '357']
                    ], $matchedParameters, [
                        '5',
                        '33'
                    ]);
            }
        });

        $records = $this->object->__call('fetchByFkOthertableRole', [["extra" => '5']]);
        $this->assertEquals('357', $records[0]->id);
    }

    public function test__call_When_ExistingReferenceReferenceByCallWithAdditionalConditions_Expect_Value()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case "SHOW INDEX FROM MySchema.OtherTable":
                case 'SHOW FULL COLUMNS IN MySchema.OtherTable':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'OtherTable\' */ WHERE k.table_name = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'OtherTable\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'OtherTable\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

                case 'INSERT INTO MySchema.OtherTable (extra, id) VALUES (' . $matchedParameters[0] . ', ' . $matchedParameters[1] . ')':
                    return createMockPDOStatement(1, $matchedParameters, [
                        '6',
                        '33'
                    ]);

                case 'SELECT * FROM MySchema.OtherTable WHERE extra = ' . $matchedParameters[0] . ' AND id = ' . $matchedParameters[1] . '':
                    return createMockPDOStatement([
                        ['id' => '358']
                    ], $matchedParameters, [
                        '6',
                        '33'
                    ]);
            }
        });

        $record = $this->object->__call('referenceByFkOthertableRole', [["extra" => '6']]);
        $this->assertEquals('358', $record->id);
    }

    public function test__call_When_NonExistingReference_Expect_Value() {
        $this->expectException('\PHPUnit\Framework\Error\Error');
        $this->expectExceptionMessageRegExp('/^Reference does not exist/');
        $this->object->__call('fetchByFkOthertableRoleWhichActuallyDoesNotExist', [["extra" => '5']]);
    }

    public function test__call_When_InvalidCallToMagicMethod_Expect_Null() {
        $this->assertNull($this->object->__call('InvalidCall', []));
    }
}
