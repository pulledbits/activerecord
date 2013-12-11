<?php

namespace Behavior;

/**
 * Description of Annotation
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Annotations
{

    /**
     *
     * @var \Behavior\Factory
     */
    protected $factory;
    
    /**
     *
     * @var \Behavior\Annotations\Annotation[]
     */
    protected $annotations = array();

    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    public function addAnnotation(Annotations\Annotation $annotation)
    {
        $this->annotations[] = $annotation;
    }

}
