<?php

namespace ActiveRecord;

class Record
{

    /**
     * @var \ActiveRecord\Asset
     */
    private $asset = NULL;

    /**
     * @var array
     */
    private $primaryKey = NULL;

    /**
     * @var array
     */
    private $values = NULL;

    /**
     * @param \ActiveRecord\Asset $asset
     * @param array $values
     */

    /**
     * @var \ActiveRecord\MetaRecord
     */
    private $metaRecord;

    public function __construct(\ActiveRecord\Schema\Asset $asset, array $primaryKey, \ActiveRecord\MetaRecord $metaRecord, array $values)
    {
        $this->asset = $asset;
        $this->primaryKey = $primaryKey;
        $this->values = $values;

        $this->metaRecord = $metaRecord;
    }

    /**
     * @param string $property
     */
    public function __get($property)
    {
        return $this->values[$property];
    }

    /**
     * @param string $property
     * @param string $value
     */
    public function __set($property, $value)
    {
        if (count($this->asset->update([$property => $this->values[$property]], $this->primaryKey)) > 0) {
            $this->values[$property] = $value;
        }
    }

    /**
     */
    public function delete()
    {
        return $this->asset->delete($this->primaryKey);
    }

    public function __call(string $method, array $arguments)
    {
        if (substr($method, 0, 6) === 'fetchBy') {
            $reference = $this->metaRecord->references()[substr($method, 6)];
            $fkColumns = array_keys($reference['where']);
            $fkLocalColumns = array_values($reference['where']);
            return $this->asset->selectFrom($reference['table'], $fkColumns, array_combine($fkColumns, array_slice_key($this->values, $fkLocalColumns)));
        }
        return call_user_func_array([$this->metaRecord, $method], $arguments);
    }

}