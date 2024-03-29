<?php

namespace Tbn\GetterTraitBundle\Generator;

use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\String\Inflector\EnglishInflector;

abstract class AbstractPropertyGenerator
{
    protected EnglishInflector $inflector;

    public function __construct()
    {
        $this->inflector = new EnglishInflector();
    }

    protected function convertType(Type $type): string
    {
        switch ($type->getBuiltinType()) {
            case 'object':
                if (is_null($type->getClassName())) {
                    return 'object';
                }
                return '\\'.$type->getClassName();
        }

        return $type->getBuiltinType();
    }
}
