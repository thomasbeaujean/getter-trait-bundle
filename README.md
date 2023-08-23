# Getter and Setter Generator

Symfony Bundle that generates getters and setters inside a trait.

Why ? Basics getters brings no information about the class.

The classes are quickly long for no "business" reason.

Having only the redefined method allows to spot the method with business purpose.

# Installation

```bash
composer require --dev "tbn/getter-trait-bundle"
```

In `config/bundles.php`, add the bundle to the dev environment
```php
return [
    ...
    tbn\GetterTraitBundle\GetterTraitBundle::class => ['dev' => true,],
];
```

# Usage

## Add the `GetSetTrait` attribute to your class

```php
use tbn\GetterTraitBundle\Attributes\GetSetTrait; // add this line

#[GetSetTrait] // add this line
class MyClass
{
    private String $name;
}
```

## Run the `./bin/console generate:getter:traits` command

A new file is automatically generated in the same folder and contains the getters and setters for the `MyClass` class.

> When a property is modified or added, you have to re-run the command in order to update the traits.

## Use the generated trait.

```php
#[GetSetTrait]
class MyClass
{
    use MyClassTrait; // add this line

    private String $name;
}
```

## I need to redefine the method

Just copy/paste the generated code inside your class and update it to your needs.

# More examples

[Example folder](./tests/src/Entity)
