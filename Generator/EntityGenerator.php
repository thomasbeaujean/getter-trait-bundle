<?php

namespace tbn\GetterTraitBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

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
        private LoggerInterface $logger,
        private PropertyTypeExtractorInterface $extractor,
        private ArrayConstructorGenerator $doctrineConstructorGenerator,
    ) {
    }

    private function generateEntityClass(ReflectionClass $reflectionClass, array $types, bool $useBody = true): string
    {
        $placeHolders = [
            '<namespace>',
            '<entityClassName>',
            '<entityBody>',
        ];

        $replacements = [
            $reflectionClass->getNamespaceName(),
            $this->generateEntityClassName($reflectionClass),
        ];

        if ($useBody) {
            $replacements[] = $this->generateEntityBody($reflectionClass, $types);
        }

        return str_replace($placeHolders, $replacements, static::$classTemplate);
    }

    public function writeEntityClass(ReflectionClass $reflectionClass, array $types): void
    {
        $this->logger->info('ENTITY: '.$reflectionClass->getName());
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

    private function generateEntityConstructor(ReflectionClass $reflectionClass, array $types): string
    {
        $arrayConstructorContent = $this->doctrineConstructorGenerator->generate($reflectionClass, $types);

        $content = $arrayConstructorContent;
        if ($arrayConstructorContent !== '') {
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

        foreach ($reflectionClass->getProperties() as $property) {
            $propertyName = $property->getName();
            $this->logger->info('PROPERTY: '.$propertyName);
            $types = $this->extractor->getTypes($reflectionClass->getName(), $propertyName);

            // getter setter
            foreach ($types as $type) {
                $methods[] = $setGenerator->generate($propertyName, $type);
                $methods[] = $getGenerator->generate($propertyName, $type);
            }

            // array add remove
            foreach ($types as $type) {
                if (!$type->isCollection()) {
                    continue;
                }

                $types = $type->getCollectionValueTypes();

                if (count($types) > 0) {
                    $elementType = $type->getCollectionValueTypes()[0];
                    $methods[] = $addGenerator->generate($reflectionClass->getShortName(), $propertyName, $elementType);

                    $elementType = $type->getCollectionValueTypes()[0];
                    $methods[] = $removeGenerator->generate($propertyName, $elementType);
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
