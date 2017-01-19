<?php
namespace ActiveRecord\Record;

trait ReadableTrait {

    /**
     * @var \ActiveRecord\Asset
     */
    private $table = NULL;

    /**
     * @var array
     */
    private $values = NULL;

    /**
     * @param \ActiveRecord\Asset $table
     * @param array $values
     */
    public function __construct(\ActiveRecord\Asset $table, array $values) {
        $this->table = $table;
        $this->values = $values;
    }

    /**
     * @param string $property
     */
    public function __get($property) {
        return $this->values[$property];
    }

    public function primaryKey() {
        return $this->values;
    }
}
