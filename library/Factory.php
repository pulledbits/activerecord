<?php

namespace Behavior;

/**
 * Description of Factory
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Factory
{
    public function makeAnnotatedFactory()
    {
        return new Annotated\Factory($this);
    }
    
    public function makeAnnotationsFactory()
    {
        return new Annotations\Factory($this);
    }
    
    public function makePHPFactory()
    {
        return new PHP\Factory($this);
    }
}
