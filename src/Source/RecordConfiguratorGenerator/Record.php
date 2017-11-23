<?php
namespace pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;

use Psr\Http\Message\StreamInterface;
use pulledbits\ActiveRecord\Source\RecordConfiguratorGenerator;
use pulledbits\ActiveRecord\Source\TableDescription;

final class Record implements RecordConfiguratorGenerator
{
    const NEWLINE = PHP_EOL;

    private $entityIdentifier;

    private $requiredAttributeIdentifiers;

    private $references;

    public function __construct(TableDescription $entityDescription)
    {
        $this->entityIdentifier = $entityDescription->identifier;
        $this->requiredAttributeIdentifiers = $entityDescription->requiredAttributeIdentifiers;
        foreach ($entityDescription->references as $referenceIdentifier => $reference) {
            $this->references[$referenceIdentifier] = [
                'table' => $reference['table'],
                'where' => $reference['where']
            ];
        }
    }

    public function generateConfigurator(StreamInterface $stream) : void
    {
        $stream->write(self::NEWLINE . '$configurator = new \\pulledbits\\ActiveRecord\\RecordConfigurator($recordFactory);');
        $stream->write(self::NEWLINE . "\$configurator->identifiedBy(['" . join("', '", $this->entityIdentifier) . "']);");

        if (count($this->requiredAttributeIdentifiers) > 0) {
            $stream->write(self::NEWLINE . "\$configurator->requires(['" . join("', '", $this->requiredAttributeIdentifiers) . "']);");
        }

        if (count($this->references) > 0) {
            foreach ($this->references as $referenceIdentifier => $reference) {
                $where = [];
                foreach ($reference['where'] as $referencedAttributeIdentifier => $localAttributeIdentifier) {
                    $where[] = '\'' . $referencedAttributeIdentifier . '\' => \'' . $localAttributeIdentifier . '\'';
                }
                $stream->write(self::NEWLINE . "\$configurator->references('" . $referenceIdentifier . "', '" . $reference['table'] . "', [" . join(", ", $where) . "]);");
            }
        }
        $stream->write(self::NEWLINE . "return \$configurator;");
    }
}