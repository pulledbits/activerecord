<?php


namespace pulledbits\ActiveRecord\SQL;


class Where
{

    private $extractParametersSQL;
    private $extractParameters;

    public function __construct(array $extractParametersSQL, array $extractParameters)
    {
        $this->extractParametersSQL = $extractParametersSQL;
        $this->extractParameters = $extractParameters;
    }

    public function __toString() : string
    {
        if (count($this->extractParametersSQL) === 0) {
            return '';
        }
        return " WHERE " . join(" AND ", $this->extractParametersSQL);
    }

    public function parameters() : array
    {
        if (count($this->extractParametersSQL) === 0) {
            return [];
        }
        return $this->extractParameters;
    }
}