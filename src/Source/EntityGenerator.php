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
            $references[] = '$record->references(\'' . $referenceIdentifier . '\', \'' . $reference['table'] . '\', [' . join('\', \'', $where). ']);';
        }

        return '<?php return function(\pulledbits\ActiveRecord\Schema $schema, string $entityTypeIdentifier) {
    $record = new \pulledbits\ActiveRecord\Entity($schema, $entityTypeIdentifier, \'' . $this->entityTypeIdentifier . '\');
    $record->requires([\'' . join('\', \'', $this->requiredAttributeIdentifiers) . '\']);
    ' . join(PHP_EOL . '    ', $references) . '
    return $record;
};';
    }
}