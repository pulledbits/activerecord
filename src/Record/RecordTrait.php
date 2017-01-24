<?php
namespace ActiveRecord\Record;

trait RecordTrait {

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
    public function __construct(\ActiveRecord\Schema\Asset $table, array $values) {
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
    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value) {
        if (count($this->table->update([$property => $this->values[$property]], $this->primaryKey())) > 0) {
            $this->values[$property] = $value;
        }
    }

    /**
     */
    public function delete() {
        return $this->table->delete($this->primaryKey());
    }
}
