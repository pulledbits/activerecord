<?php


namespace pulledbits\ActiveRecord;


class EntityTypes implements \Iterator
{

    private $entityTypes;

    public function __construct(Result $result)
    {
        $this->entityTypes = $result->fetchAll();
    }

    public function current()
    {
        return current($this->entityTypes);
    }

    public function next()
    {
        return next($this->entityTypes);
    }

    public function key()
    {
        return key($this->entityTypes);
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        return reset($this->entityTypes);
    }
}