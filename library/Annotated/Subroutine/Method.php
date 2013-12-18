<?php

namespace Behavior\Annotated\Subroutine;

/**
 * Description of AnnotatedMethod
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Method extends \Behavior\Annotated\Subroutine
{
    protected function makeAnnotations(\Behavior\Annotations\Factory $annotationsFactory)
    {
        return $annotationsFactory->makeAnnotationsForReflectionMethod($this->reflector);
    }

}
