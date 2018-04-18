<?php

namespace pulledbits\ActiveRecord\SQL\MySQL;

use pulledbits\ActiveRecord\SQL\Connection;
use pulledbits\ActiveRecord\SQL\Statement;
use function pulledbits\ActiveRecord\Test\createColumnResult;
use function pulledbits\ActiveRecord\Test\createConstraintResult;
use function pulledbits\ActiveRecord\Test\createIndexResult;
use function pulledbits\ActiveRecord\Test\createMockPDOCallback;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;
use function pulledbits\ActiveRecord\Test\createTableResult;
use function pulledbits\ActiveRecord\Test\createViewResult;

class EntityTypesTest extends \PHPUnit\Framework\TestCase
{
    private $schema;
    private $pdo;

    protected function setUp() {
        $this->pdo = createMockPDOCallback();
        $this->pdo->callback(function(string $query) {
            switch ($query) {
                case 'SHOW FULL TABLES IN MySchema':
                    return createMockPDOStatement([
                        createTableResult('MySchema', 'MyTable'),
                        createViewResult('MySchema', 'MyTable_MyView')
                    ]);

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
    }

    public function testRetrieveTableDescription_When_EntityNotExists_Expect_EmptyTableDescription()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.NotExisting':
                case 'SHOW FULL COLUMNS IN MySchema.NotExisting':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'NotExisting\' */ WHERE k.table_name = \'NotExisting\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'NotExisting\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'NotExisting\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

            }
        });

        $object = new EntityTypes($this->schema, new Query\Result(new Statement(createMockPDOStatement([]))));

        $this->assertEquals(new EntityType($this->schema, 'NotExisting'), $object->makeRecordType('NotExisting'));
    }

    public function testRetrieveTableDescription_When_EntityIsView_Expect_EmptyTableDescriptionForViewIdentifier()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyView':
                case 'SHOW FULL COLUMNS IN MySchema.MyView':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyView\' */ WHERE k.table_name = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyView\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

            }
        });

        $object = new EntityTypes($this->schema, new Query\Result(new Statement(createMockPDOStatement([
            createViewResult('MySchema', 'MyView')
        ]))));

        $this->assertEquals(new EntityType($this->schema, 'MyView'), $object->makeRecordType('MyView'));
    }

    public function testRetrieveTableDescription_When_EntityIsViewWrappedAroundOtherTable_Expect_EntityTypeForWrappedTable()
    {
        $this->pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW INDEX FROM MySchema.MyTable_MyView':
                case 'SHOW FULL COLUMNS IN MySchema.MyTable_MyView':
                case '(SELECT DISTINCT k.`CONSTRAINT_NAME`, `k`.`TABLE_NAME`, k.`COLUMN_NAME`, k.`REFERENCED_TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.table_name = \'MyTable_MyView\' */ WHERE k.table_name = \'MyTable_MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL) UNION ALL (SELECT DISTINCT k.`CONSTRAINT_NAME`, k.`REFERENCED_TABLE_NAME` AS `TABLE_NAME`, k.`REFERENCED_COLUMN_NAME` AS `COLUMN_NAME`, `k`.`TABLE_NAME` AS `REFERENCED_TABLE_NAME`, k.`COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` /**!50116 , c.update_rule, c.delete_rule */ FROM information_schema.key_column_usage k /**!50116 INNER JOIN information_schema.referential_constraints c ON   c.constraint_name = k.constraint_name AND   c.`REFERENCED_TABLE_NAME` = \'MyTable_MyView\' */ WHERE k.`REFERENCED_TABLE_NAME` = \'MyTable_MyView\' AND k.table_schema = \'MySchema\' /**!50116 AND c.constraint_schema = \'MySchema\' */ AND k.`REFERENCED_COLUMN_NAME` is not NULL)':
                    return createMockPDOStatement([]);

            }
        });

        $object = new EntityTypes($this->schema);

        $this->assertEquals(new EntityType($this->schema, 'MyTable'), $object->makeRecordType('MyTable_MyView'));
    }
}
