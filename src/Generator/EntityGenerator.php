<?php

namespace Tbn\GetterTraitBundle\Generator;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use Symfony\Component\TypeInfo\Type\CollectionType;

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
        private Extractor $extractor,
        private ArrayConstructorGenerator $doctrineConstructorGenerator,
        private GetGenerator $getGenerator,
        private SetGenerator $setGenerator,
        private AddGenerator $addGenerator,
        private RemoveGenerator $removeGenerator,
    ) {
    }

    private function generateEntityClass(ReflectionClass $reflectionClass, bool $useBody = true): string
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
            $replacements[] = $this->generateEntityBody($reflectionClass);
        }

        return str_replace($placeHolders, $replacements, static::$classTemplate);
    }

    public function writeEntityClass(ReflectionClass $reflectionClass): void
    {
        $this->logger->info('ENTITY: '.$reflectionClass->getName());
        $content = $this->generateEntityClass($reflectionClass);
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

    private function generateEntityConstructor(ReflectionClass $reflectionClass): string
    {
        $arrayConstructorContent = $this->doctrineConstructorGenerator->generate($reflectionClass);

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

        foreach ($reflectionClass->getProperties() as $property) {
            // do not handle inherited properties
            if ($property->getDeclaringClass()->getName() !== $reflectionClass->getName()) {
                continue;
            }

            $propertyName = $property->getName();
            $this->logger->info('PROPERTY: '.$propertyName);
            $type = $this->extractor->getType($reflectionClass->getName(), $propertyName);

            // the mixed type gives a null value
            if (null === $type) {
                continue;
            }

            // getter setter
            $methods[] = $this->setGenerator->generate($propertyName, $type);
            $methods[] = $this->getGenerator->generate($propertyName, $type);

            if (! ($type instanceof CollectionType)) {
                continue;
            }
            $methods[] = $this->addGenerator->generate(
                $reflectionClass->getShortName(),
                $propertyName,
                $type,
            );
            $methods[] = $this->removeGenerator->generate(
                $propertyName,
                $type,
            );
        }

        return implode("\n\n", array_filter($methods));
    }

    protected function generateEntityBody(ReflectionClass $reflectionClass): string
    {
        $stubMethods = $this->generateEntityStubMethods($reflectionClass);
        $code = [];
        $code[] = $this->generateEntityConstructor($reflectionClass);

        if ($stubMethods) {
            $code[] = $stubMethods;
        }

        return implode("\n", $code);
    }
}
