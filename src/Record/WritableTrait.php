<?php
namespace ActiveRecord\Record;

trait WritableTrait {
    use ReadableTrait;

    public function __construct(\ActiveRecord\Schema\WritableAsset $table, array $values) {
        $this->table = $table;
        $this->values = $values;
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
