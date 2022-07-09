<?php

namespace tbn\GetterTraitBundle\Generator;

use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\PropertyInfo\Type;

abstract class AbstractPropertyGenerator
{
    protected Inflector $inflector;

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    protected function convertType(Type $type): string
    {
        switch ($type->getBuiltinType()) {
            case 'object':
                return '\\'.$type->getClassName();
        }

        return $type->getBuiltinType();
    }
}
