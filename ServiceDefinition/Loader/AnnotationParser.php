<?php
/*
 * This file is part of the WebServiceBundle.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Bundle\WebServiceBundle\ServiceDefinition\Loader;

use Doctrine\Common\Annotations\Lexer;
use Doctrine\Common\Annotations\Parser;

/**
 * AnnotationParser allows multiple annotations of the same class to be present.
 *
 * @author Christian Kerl <christian-kerl@web.de>
 */
class AnnotationParser extends Parser
{
    /**
     * Annotations ::= Annotation {[ "*" ]* [Annotation]}*
     *
     * @return array
     */
    public function Annotations()
    {
        $this->isNestedAnnotation = false;

        $annotations = array();
        $annot = $this->Annotation();

        if ($annot !== false) {
            $annotations[get_class($annot)][] = $annot;
            $this->getLexer()->skipUntil(Lexer::T_AT);
        }

        while ($this->getLexer()->lookahead !== null && $this->getLexer()->isNextToken(Lexer::T_AT)) {
            $this->isNestedAnnotation = false;
            $annot = $this->Annotation();

            if ($annot !== false) {
                $annotations[get_class($annot)][] = $annot;
                $this->getLexer()->skipUntil(Lexer::T_AT);
            }
        }

        return $annotations;
    }
}