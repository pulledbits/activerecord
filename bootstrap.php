<?php
namespace pulledbits\ActiveRecord;

return new class {
    public function __construct()
    {
        require getcwd() . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
    }
};