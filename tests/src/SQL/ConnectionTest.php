<?php

namespace pulledbits\ActiveRecord\SQL;


use pulledbits\ActiveRecord\SQL\MySQL\QueryFactory;
use pulledbits\ActiveRecord\SQL\MySQL\Schema;
use function pulledbits\ActiveRecord\Test\createMockPDOCallback;
use function pulledbits\ActiveRecord\Test\createMockPDOStatement;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{

    public function testSchema_When_DefaultSchema_ExpectNewSchema()
    {
        $pdo = createMockPDOCallback();
        $connection = new Connection($pdo);
        $pdo->callback(function(string $query, array $matchedParameters) {
            switch ($query) {
                case 'SHOW FULL TABLES IN MySchema':
                    return createMockPDOStatement([]);
            }
        });

        $expectedSchema = new Schema(new QueryFactory($connection), 'MySchema');
        $this->assertEquals($expectedSchema, $connection->schema('MySchema'));
    }
}
