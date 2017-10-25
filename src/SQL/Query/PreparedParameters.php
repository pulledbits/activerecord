<?php


namespace pulledbits\ActiveRecord\SQL\Query;


class PreparedParameters implements \Countable
{
    const PP_COLUMN = 'column';
    const PP_VALUE = 'value';
    const PP_SQL = 'sql';
    const PP_PARAM = 'parameter';
    const PP_PARAMS = 'parameters';

    private $preparedParameters;

    /**
     * PreparedParameters constructor.
     * @param array $parameters
     */
    public function __construct($parameters)
    {
        $this->preparedParameters = [];
        foreach ($parameters as $localColumn => $value) {
            $this->preparedParameters[] = [
                self::PP_COLUMN => $localColumn,
                self::PP_VALUE => $value,
                self::PP_PARAM => ":" . uniqid()
            ];
        }
    }

    private function extract(string $type) {
        return array_map(function(array $preparedParameter) use ($type) { return $preparedParameter[$type]; }, $this->preparedParameters);
    }

    public function extractParameters() {
        return array_combine($this->extract(self::PP_PARAM), $this->extract(self::PP_VALUE));
    }

    public function extractParametersSQL() {
        return array_map(function($preparedParameter) { return $preparedParameter[self::PP_COLUMN] . " = " . $preparedParameter[self::PP_PARAM]; }, $this->preparedParameters);
    }

    public function extractParameterizedValues()
    {
        return $this->extract(self::PP_PARAM);
    }

    public function extractColumns()
    {
        return $this->extract(self::PP_COLUMN);
    }

    public function count()
    {
        return count($this->preparedParameters);
    }
}