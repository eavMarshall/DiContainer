<?php
/**
 * @author     Elliott Marshall
 * @copyright  2020
 * @license    MIT
 * @version    1.0.1
 */

namespace Di;

use ReflectionClass;
use function array_merge;

class DIContainer
{
    private static $thisInstance;
    private $overrideRules;
    private $singeInstancesCache = [];
    private $constructorCache = [];

    public function __construct($overrideRules = [])
    {
        $this->overrideRules = array_merge($overrideRules, [self::class => function () { return $this; }]);
    }

    public static function getInstance()
    {
        return self::$thisInstance ?: self::$thisInstance = new self();
    }

    public function addOverrideRule($class, $overrideFunction)
    {
        return new self(array_merge($this->overrideRules, [$class => $overrideFunction]));
    }

    public function getInstanceOf($class, array $parameters = null)
    {
        if (!$class) return null;
        if (isset($this->overrideRules[$class])) return $this->overrideRules[$class]();

        if ($parameters === null) {
            if (isset($this->singeInstancesCache[$class])) return $this->singeInstancesCache[$class];
            return $this->_getInstance($class);
        }

        return new $class(...$parameters);
    }

    private function _getInstance($class)
    {
        if (isset($this->constructorCache[$class])) {
            if (empty($this->constructorCache[$class])) return new $class();

            $paramInstances = [];
            foreach ($this->constructorCache[$class] as $type) {
                $paramInstances[] = $this->getInstanceOf($type);
            }

            return new $class(...$paramInstances);
        }

        return $this->_getInstanceOnFirstRun($class);
    }

    private function _getInstanceOnFirstRun($class)
    {
        $reflectionClass = new ReflectionClass($class);
        $constructor = $reflectionClass->getConstructor();
        $this->constructorCache[$class] = [];
        $initialParamInstances = [];

        if ($constructor !== null) {
            $parameters = $constructor->getParameters();

            foreach ($parameters as $parameter) {
                $type = $parameter->getClass();
                $initialParamInstances[] = $this->getInstanceOf(
                    $this->constructorCache[$class][] = $type ? $type->getName() : null
                );
            }
        }

        $instance = new $class(...$initialParamInstances);

        if ($reflectionClass->implementsInterface(SingleInstance::class)) {
            $this->singeInstancesCache[$class] = $instance;
        }

        return $instance;
    }
}
