<?php

namespace Behavior;

/**
 * Description of Factory
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Factory
{
    
    public function makeAnnotationsFactory()
    {
        return new Annotations\Factory($this);
    }
    
}
