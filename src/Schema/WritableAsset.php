<?php
namespace ActiveRecord\Schema;

interface WritableAsset extends ReadableAsset {
    public function insert(array $values);
    public function update(array $setParameters, array $whereParameters);
    public function delete(array $whereParameters);
}