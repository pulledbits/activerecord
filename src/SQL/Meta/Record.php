<?php
namespace pulledbits\ActiveRecord\SQL\Meta;

use pulledbits\ActiveRecord\RecordConfigurator;
use pulledbits\ActiveRecord\RecordType;

final class Record implements RecordConfigurator
{
    private $recordType;

    public function __construct(RecordType $recordType)
    {
        $this->recordType = $recordType;
    }

    public function configure(): \pulledbits\ActiveRecord\Record
    {
        return $this->recordType->makeRecord();
    }
}