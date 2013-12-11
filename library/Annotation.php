<?php

namespace Behavior;

/**
 * Description of DocCommentParser
 *
 * @author Rik Meijer <rmeijer@saa.nl>
 */
class Annotation
{

    static function findNextAnnotation($string)
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
            return self::findNextAnnotation(substr($string, $nextAt + 1));
        }
        return $nextAt;
    }

    static function parseDocComment($string)
    {
        $currentAnnotationPosition = self::findNextAnnotation($string);

        $annotations = array();
        while ($currentAnnotationPosition < strlen($string)) {
            $string = substr($string, $currentAnnotationPosition + 1);

            $currentAnnotationPosition = self::findNextAnnotation($string);

            $annotations[] = substr($string, 0, $currentAnnotationPosition);
        }
        return $annotations;
    }

}
