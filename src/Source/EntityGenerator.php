<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:33
 */

namespace pulledbits\ActiveRecord\Source;


class EntityGenerator
{
    const NEWLINE = "\n    ";

    private $entityTypeIdentifier;
    private $requiredAttributeIdentifiers;
    private $references;

    /**
     * WrappedEntityGenerator constructor.
     */
    public function __construct(string $entityTypeIdentifier, array $requiredAttributeIdentifiers, array $references)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
        $this->requiredAttributeIdentifiers = $requiredAttributeIdentifiers;
        $this->references = $references;
    }

    public function generate()
    {

        $references = [];
        foreach ($this->references as $referenceIdentifier => $reference) {
            $where = [];
            foreach ($reference['where'] as $referencedAttributeIdentifier => $localAttributeIdentifier) {
                $where[] = '\'' . $referencedAttributeIdentifier . '\' => \'' . $localAttributeIdentifier . '\'';
            }
            $references[] = self::NEWLINE . "\$record->references('" . $referenceIdentifier . "', '" . $reference['table'] . "', [" . join("', '", $where). ']);';
        }

        $requires = '';
        if (count($this->requiredAttributeIdentifiers) > 0) {
            $requires = self::NEWLINE . "\$record->requires(['" . join("', '", $this->requiredAttributeIdentifiers) . "']);";
        }

        return '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' .
            self::NEWLINE . '$record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, \'' . $this->entityTypeIdentifier . '\');' .
            $requires .
            join(PHP_EOL . '    ', $references) .
            self::NEWLINE . 'return $record;' . "\n" . '};';
    }
}