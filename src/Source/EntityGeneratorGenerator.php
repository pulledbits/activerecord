<?php
namespace pulledbits\ActiveRecord\Source;

use Psr\Http\Message\StreamInterface;

final class EntityGeneratorGenerator implements GeneratorGenerator
{
    const NEWLINE = PHP_EOL . "    ";

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(array $entityIdentifier)
    {
        $this->entityIdentifier = $entityIdentifier;
        $this->requiredAttributeIdentifiers = [];
        $this->references = [];
    }

    public function requires(array $requiredAttributeIdentifiers) {
        $this->requiredAttributeIdentifiers = $requiredAttributeIdentifiers;
    }

    public function references(string $referenceIdentifier, string $referencedEntityIdentifier, array $conditions)
    {
        $this->references[$referenceIdentifier] = [
            'table' => $referencedEntityIdentifier,
            'where' => $conditions
        ];
    }

    public function generate(StreamInterface $stream) : void
    {

        $references = [];
        if (count($this->references) > 0) {
            $references[] = '';
            foreach ($this->references as $referenceIdentifier => $reference) {
                $where = [];
                foreach ($reference['where'] as $referencedAttributeIdentifier => $localAttributeIdentifier) {
                    $where[] = '\'' . $referencedAttributeIdentifier . '\' => \'' . $localAttributeIdentifier . '\'';
                }
                $references[] = "\$record->references('" . $referenceIdentifier . "', '" . $reference['table'] . "', [" . join(", ", $where) . "]);";
            }
        }

        $requires = '';
        if (count($this->requiredAttributeIdentifiers) > 0) {
            $requires = self::NEWLINE . "\$record->requires(['" . join("', '", $this->requiredAttributeIdentifiers) . "']);";
        }

        $stream->write('<?php return function(\\pulledbits\\ActiveRecord\\Entity $record) {' .
            self::NEWLINE . "\$record->identifiedBy(['" . join("', '", $this->entityIdentifier) . "']);" .
            $requires .
            join(self::NEWLINE, $references) .
            self::NEWLINE . 'return $record;' . "\n" . '};');
    }
}