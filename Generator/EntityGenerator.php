<?php

namespace tbn\GetterTraitBundle\Generator;

use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;

class EntityGenerator
{
    private static string $classTemplate = '<?php

namespace <namespace>;

<entityClassName>
{
<entityBody>
}
';

    public function __construct(
        private PropertyInfoExtractorInterface $extractor,
        private ArrayConstructorGenerator $doctrineConstructorGenerator
    ) {
    }

    private function generateEntityClass(ReflectionClass $reflectionClass, array $types): string
    {
        $placeHolders = [
            '<namespace>',
            '<entityClassName>',
            '<entityBody>',
        ];

        $replacements = [
            $reflectionClass->getNamespaceName(),
            $this->generateEntityClassName($reflectionClass),
            $this->generateEntityBody($reflectionClass, $types),
        ];

        return str_replace($placeHolders, $replacements, static::$classTemplate);
    }

    public function writeEntityClass(ReflectionClass $reflectionClass, array $types): void
    {
        $content = $this->generateEntityClass($reflectionClass, $types);
        $content = $this->removeTrailingSpacesAndTab($content);
        $cleanedContent = $this->removeDoubleEndLine($content);

        $targetFileName = $this->getTraitFileName($reflectionClass->getFileName());
        file_put_contents($targetFileName, $cleanedContent);
    }

    private function getTraitFileName(string $originFileName): string
    {
        // remove .php
        $withoutExtension = substr($originFileName, 0, strlen($originFileName) - 4);

        // add Trait.php
        return $withoutExtension.'Trait.php';
    }

    private function generateEntityClassName(ReflectionClass $reflectionClass): string
    {
        return 'trait '.$reflectionClass->getShortName().'Trait';
    }

    private function hasMethod(ReflectionClass $reflectionClass, string $method): bool
    {
        if (!($reflectionClass->hasMethod($method))) {
            // neither the class nor the trait
            return false;
        }

        // get the trait reflexion
        $traitClass = $reflectionClass->getNamespaceName().'/'.$reflectionClass->getShortName().'Trait';
        $traitReflexion =  new ReflectionClass(str_replace("/", "\\",$traitClass));

        if ($traitReflexion->hasMethod($method)) {
            // the trait have the method
            // so the class does not own it
            return false;
        }

        return true;
    }

    private function generateEntityConstructor(ReflectionClass $reflectionClass, array $types): string
    {
        $arrayConstructorContent = $this->doctrineConstructorGenerator->generate($reflectionClass, $types);

        $content = $arrayConstructorContent;
        if ($arrayConstructorContent !== '' && !$this->hasMethod($reflectionClass, '__construct')) {
            $content .= "\n";
            $constructorGenerator = new ConstructorGenerator();
            $content .= $constructorGenerator->generate();
        }

        return $content;
    }

    protected function removeTrailingSpacesAndTab($content): string
    {
        $pattern = '/[ ]*\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
    }

    private function removeDoubleEndLine($content): string
    {
        $pattern = '/\n\n/';
        $replacement = "\n";
        $cleanedContent = preg_replace($pattern, $replacement, $content);

        return $cleanedContent;
    }

    protected function generateEntityStubMethods(ReflectionClass $reflectionClass): string
    {
        $methods = [];

        $getGenerator = new GetGenerator();
        $setGenerator = new SetGenerator();

        $addGenerator = new AddGenerator();
        $removeGenerator = new RemoveGenerator();

        $properties = $this->extractor->getProperties($reflectionClass->getName());
        foreach ($properties as $property) {
            $types = $this->extractor->getTypes($reflectionClass->getName(), $property);

            // getter setter
            foreach ($types as $type) {
                if (!$this->hasMethod($reflectionClass, $setGenerator->getMethodName($property))) {
                    $methods[] = $setGenerator->generate($property, $type);
                }
                if (!$this->hasMethod($reflectionClass, $getGenerator->getMethodName($property))) {
                    $methods[] = $getGenerator->generate($property, $type);
                }
            }

            // array add remove
            foreach ($types as $type) {
                if (!$type->isCollection()) {
                    continue;
                }

                if (!$this->hasMethod($reflectionClass, $addGenerator->getMethodName($property))) {
                    $elementType = $type->getCollectionValueTypes()[0];
                    $methods[] = $addGenerator->generate($reflectionClass->getShortName(), $property, $elementType);
                }
                if (!$this->hasMethod($reflectionClass, $removeGenerator->getMethodName($property))) {
                    $elementType = $type->getCollectionValueTypes()[0];
                    $methods[] = $removeGenerator->generate($property, $elementType);
                }
            }
        }

        return implode("\n\n", array_filter($methods));
    }

    protected function generateEntityBody(ReflectionClass $reflectionClass, array $types): string
    {
        $stubMethods = $this->generateEntityStubMethods($reflectionClass);
        $code = [];
        $code[] = $this->generateEntityConstructor($reflectionClass, $types);

        if ($stubMethods) {
            $code[] = $stubMethods;
        }

        return implode("\n", $code);
    }
}
