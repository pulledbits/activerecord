<?php


namespace pulledbits\ActiveRecord\SQL\MySQL\Query;


class Where
{

    private $conditions;

    public function __construct(PreparedParameters $conditions)
    {
        $this->conditions = $conditions;
    }

    public function __toString(): string
    {
        if (count($this->conditions) === 0) {
            return '';
        }
        return " WHERE " . join(" AND ", $this->conditions->extractParametersSQL());
    }

    public function parameters(): array
    {
        if (count($this->conditions) === 0) {
            return [];
        }
        return $this->conditions->extractParameters();
    }
}