<?php
/**
 * @author     Elliott Marshall                                            *
 * @copyright  2019
 * @license    MIT
 * @version    1.0
 */
namespace Di;

use Exception;
use ReflectionClass;

class DIContainer
{
    public function resetOverrideRules()
    {
        $this->overrideRules = [];
        $this->addOverrideRule( self::class, function () { return self::getInstance(); });
        //$this->addOverrideRule( MySingleton::class, function () { return MySingleton::getInstance(); });
    }

    private static $thisInstance;
    private $overrideRules = [];
    private $implementsNewInstanceCache = [];
    private $singeInstancesCache = [];
    private $constructorCache = [];

    protected function __construct()
    {
        $this->resetOverrideRules();
    }

    public static function getInstance()
    {
        return self::$thisInstance ?: self::$thisInstance = new self();
    }

    /**
     * Put here so developers don't have to call DIContainer::getInstance()->getInstanceOf(<<namespace class name>>)
     * @param $class
     * @return mixed
     * @throws Exception
     */
    public static function loadInstanceOf($class)
    {
        return self::getInstance()->getInstanceOf($class);
    }

    public function addOverrideRule($class, $overrideFunction)
    {
        $this->overrideRules[$class] = $overrideFunction;
    }

    public function getInstanceOf($class)
    {
        if (!isset($this->implementsNewInstanceCache[$class])) {
            $implements = class_implements($class, true);
            $this->implementsNewInstanceCache[$class] = !empty($implements)
                && !in_array(NewInstance::class, $implements, false)
                && in_array(SingleInstance::class, $implements, false);
        }

        if ($this->implementsNewInstanceCache[$class]) {
            return $this->singeInstancesCache[$class]
                ?? $this->singeInstancesCache[$class] = new $class(...$this->getParams($class));
        }

        return new $class(...$this->getParams($class));
    }

    private function getParams($classPath)
    {
        if (!isset($this->constructorCache[$classPath])) {
            return $this->getParamOnFirstRun($classPath);
        }

        $paramInstances = [];
        foreach ($this->constructorCache[$classPath] as $type) {
            $paramInstances[] = $this->getParamInstance($type);
        }

        return $paramInstances;
    }

    private function getParamOnFirstRun($classPath)
    {
        $constructor = (new ReflectionClass($classPath))->getConstructor();
        $parameters = $constructor ? $constructor->getParameters() ?: [] : [];
        $this->constructorCache[$classPath] = [];
        $initialParamInstances = [];
        foreach ($parameters as $parameter) {
            $initialParamInstances[] = $this->getParamInstance($this->constructorCache[$classPath][] = $parameter->getType()->__toString());
        }

        return $initialParamInstances;
    }

    private function getParamInstance($type)
    {
        return $this->getSingletonOverrides($type) ?: $this->getInstanceOf($type);
    }

    private function getSingletonOverrides($type)
    {
        return isset($this->overrideRules[$type]) ? $this->overrideRules[$type]() : null;
    }
}