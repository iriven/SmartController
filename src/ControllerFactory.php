<?php

namespace Xocotlah\SmartController;

use League\Container\ReflectionContainer;
use League\Container\Exception\NotFoundException;
use Xocotlah\SmartController\SmartControllerInterface;

class ControllerFactory extends ReflectionContainer
{

    /**
     * Resolved controllers
     * @var array
     */
    protected $resolved = [];

    /**
     * Namespace prefix to possibly apply to controllers class names
     * @var string
     */
    protected $namespacePrefix;

    /**
     * {@inheritdoc}
     */
    public function has($id)
    {
        if (array_key_exists($id, $this->resolved)) {
            return $this->resolved[$id] !== false;
        }

        $this->resolved[$id] = false;

        if (class_exists($id) && $this->isSmartController($id)) {
            $this->resolved[$id] = $id;
            return true;
        }

        $fqcn = $this->fqcn($id);
        if (class_exists($fqcn) && $this->isSmartController($fqcn)) {
            $this->resolved[$id] = $fqcn;
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id, array $args = [])
    {
        if (! $this->has($id)) {
            throw new NotFoundException(
                sprintf('Controller (%s) is not an existing class and therefore cannot be resolved', $id)
            );
        }

        $alias = $this->resolved[$id];

        $reflector = new \ReflectionClass($alias);
        $construct = $reflector->getConstructor();

        return $reflector->newInstanceArgs(
            $this->reflectArguments($construct, $args)
        );
    }

    /**
     * Check if specified class|object implements SmartControllerInterface
     * @method isSmartController
     * @param  string|object $className
     * @return boolean
     */
    protected function isSmartController($className)
    {
        $interfaces = class_implements($className);
        return in_array(SmartControllerInterface::class, $interfaces);
    }

    /**
     * Set the default namespace prefix
     * @method setNamespacePrefix
     * @param  string $prefix
     */
    public function setNamespacePrefix($prefix)
    {
        $this->namespacePrefix = $prefix;
        return $this;
    }

    /**
     * Return FQCN based on namespacePrefix
     * @method fqcn
     * @param  string $name
     * @return string
     */
    protected function fqcn($name)
    {
        return $this->namespacePrefix.$name;
    }
}
