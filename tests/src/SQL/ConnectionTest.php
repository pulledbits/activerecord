<?php

namespace pulledbits\ActiveRecord\SQL;


use function pulledbits\ActiveRecord\Test\createMockPDOMultiple;

class ConnectionTest extends \PHPUnit\Framework\TestCase
{

    public function testSchema_When_DefaultSchema_ExpectNewSchema()
    {
        $connection = new Connection(createMockPDOMultiple([
            '/SHOW FULL TABLES IN MySchema/' => []
        ]));
        $expectedSchema = new Schema($connection, new QueryFactory(), 'MySchema');

        $this->assertEquals($expectedSchema, $connection->schema('MySchema'));
    }
}
