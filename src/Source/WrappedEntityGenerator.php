<?php
/**
 * Created by PhpStorm.
 * User: hameijer
 * Date: 23-2-17
 * Time: 15:33
 */

namespace pulledbits\ActiveRecord\Source;


class WrappedEntityGenerator
{
    private $entityTypeIdentifier;

    /**
     * WrappedEntityGenerator constructor.
     */
    public function __construct(string $entityTypeIdentifier)
    {
        $this->entityTypeIdentifier = $entityTypeIdentifier;
    }

    public function generate()
    {
        return '<?php return require __DIR__ . DIRECTORY_SEPARATOR . "' . $this->entityTypeIdentifier . '.php";';
    }
}