<?php


namespace Dontdrinkandroot\UtilsBundle\Test;

/**
 * @deprecated
 */
trait ReferenceTrait
{

    /**
     * @param string $name
     *
     * @return mixed
     */
    abstract protected function getReference($name);
}
