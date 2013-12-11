<?php

namespace Behavior\Annotations;

/**
 * Description of Annotations
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Factory
{
    
    /**
     *
     * @var \Behavior\Factory
     */
    protected $factory;
    
    public function __construct(\Behavior\Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Finds next Annotations in $string
     * @param string $string
     * @return integer
     */
    protected function findNextAnnotation($string)
    {
        $stringLength = strlen($string);
        $nextAt = strpos($string, '@');
        if ($nextAt === false) {
            return $stringLength;
        }
        $preceedingNewline = strrpos(substr($string, 0, $nextAt), "\n");
        if ($preceedingNewline === false) {
            return $stringLength;
        }

        $preceedingString = substr($string, $preceedingNewline, $nextAt - $preceedingNewline);
        if (preg_match('/^[\r\n]+\s+\*\s+$/', $preceedingString) !== 1) {
            return $this->findNextAnnotation(substr($string, $nextAt + 1));
        }
        return $nextAt;
    }
    
    /**
     * 
     * @return \Behavior\Annotations
     */
    public function makeAnnotations()
    {
        return new \Behavior\Annotations($this->factory);
    }

    /**
     * Finds all Annotations in $string and returns an \Behavior\Annotations
     * @param string $string
     * @return \Behavior\Annotations
     */
    public function makeAnnotationsForReflectionMethod(\ReflectionMethod $method)
    {
        $string = $method->getDocComment();
        
        $currentAnnotationPosition = $this->findNextAnnotation($string);

        $annotations = $this->makeAnnotations();
        while ($currentAnnotationPosition < strlen($string)) {
            $string = substr($string, $currentAnnotationPosition + 1);

            $currentAnnotationPosition = $this->findNextAnnotation($string);

            $docCommentAnnotation = trim(preg_replace('/[\r\n]+\h+\*\h*(\/$)?/', chr(10), substr($string, 0, $currentAnnotationPosition)));
            list($annotationIdentifier, $annotation) = explode(' ', $docCommentAnnotation, 2);
            
            $annotationClassIdentifier = __NAMESPACE__ . NAMESPACE_SEPARATOR . 'Annotation' . NAMESPACE_SEPARATOR . ucfirst($annotationIdentifier);
            $annotations->addAnnotation(new $annotationClassIdentifier($this, $annotation));
            
        }
        return $annotations;
    }

}
