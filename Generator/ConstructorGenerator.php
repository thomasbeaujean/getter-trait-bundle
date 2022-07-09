<?php

namespace tbn\GetterTraitBundle\Generator;

class ConstructorGenerator
{
    protected static $template =
'
    public function __construct()
    {
        $this->arrayConstruct();
    }
';

    public function generate(): string
    {
        return self::$template;
    }
}
