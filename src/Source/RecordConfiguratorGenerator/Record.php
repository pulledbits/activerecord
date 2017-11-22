<?php
namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

final class Record implements RecordConfiguratorGenerator
{
    const NEWLINE = PHP_EOL . "    ";

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(array $entityDescription)
    {
        $this->entityIdentifier = $entityDescription['identifier'];
        $this->requiredAttributeIdentifiers = $entityDescription['requiredAttributeIdentifiers'];
        foreach ($entityDescription['references'] as $referenceIdentifier => $reference) {
            $this->references[$referenceIdentifier] = [
                'table' => $reference['table'],
                'where' => $reference['where']
            ];
        }
        $this->requiredAttributeIdentifiers = $entityDescription['requiredAttributeIdentifiers'];
        $this->references = $entityDescription['references'];
    }

    public function requires(array $requiredAttributeIdentifiers) {
    }

    public function references(string $referenceIdentifier, string $referencedEntityIdentifier, array $conditions)
    {
    }

    public function generateConfigurator(StreamInterface $stream) : void
    {
        $stream->write(self::NEWLINE . 'return new class($recordFactory) implements \\pulledbits\\ActiveRecord\\RecordConfigurator {');
        $stream->write(self::NEWLINE . 'private $recordFactory;');
        $stream->write(self::NEWLINE . 'public function __construct(\\pulledbits\\ActiveRecord\\RecordFactory $recordFactory) {');
        $stream->write(self::NEWLINE . '$this->recordFactory = $recordFactory;');
        $stream->write(self::NEWLINE . '}');
        $stream->write(self::NEWLINE . 'public function configure() : \\pulledbits\\ActiveRecord\\Record {');
        $stream->write(self::NEWLINE . '$record = $this->recordFactory->makeRecord();');
        $stream->write(self::NEWLINE . "\$record->identifiedBy(['" . join("', '", $this->entityIdentifier) . "']);");

        if (count($this->requiredAttributeIdentifiers) > 0) {
            $stream->write(self::NEWLINE . "\$record->requires(['" . join("', '", $this->requiredAttributeIdentifiers) . "']);");
        }

        if (count($this->references) > 0) {
            foreach ($this->references as $referenceIdentifier => $reference) {
                $where = [];
                foreach ($reference['where'] as $referencedAttributeIdentifier => $localAttributeIdentifier) {
                    $where[] = '\'' . $referencedAttributeIdentifier . '\' => \'' . $localAttributeIdentifier . '\'';
                }
                $stream->write(self::NEWLINE . "\$record->references('" . $referenceIdentifier . "', '" . $reference['table'] . "', [" . join(", ", $where) . "]);");
            }
        }
        $stream->write(self::NEWLINE . 'return $record;' . "\n" . '}};');
    }
}