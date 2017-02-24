<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:33
 */

namespace pulledbits\ActiveRecord\Source;


class EntityGeneratorGenerator implements GeneratorGenerator
{
    const NEWLINE = PHP_EOL . "    ";

    /**
     * @var array
     */
    private $entityIdentifier;

    /**
     * @var array
     */
    private $requiredAttributeIdentifiers;

    /**
     * @var array
     */
    private $references;

    /**
     * WrappedEntityGenerator constructor.
     */
    public function __construct(array $entityIdentifier, array $requiredAttributeIdentifiers, array $references)
    {
        $this->entityIdentifier = $entityIdentifier;
        $this->requiredAttributeIdentifiers = $requiredAttributeIdentifiers;
        $this->references = $references;
    }

    public function generate()
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

        return '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {' .
            self::NEWLINE . "\$record = new \\pulledbits\\ActiveRecord\\Entity(\$schema, \$entityTypeIdentifier, ['" . join("', '", $this->entityIdentifier) . "']);" .
            $requires .
            join(self::NEWLINE, $references) .
            self::NEWLINE . 'return $record;' . "\n" . '};';
    }
}