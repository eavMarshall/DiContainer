<?php
/**
 * @author     Elliott Marshall
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
        if (isset($this->singeInstancesCache[$class])) {
            return $this->singeInstancesCache[$class];
        }

        $instance = $this->createNewClassInstance($class);

        if ($this->implementsNewInstanceCache[$class] !== false) {
            $this->singeInstancesCache[$class] = $instance;
        }

        return $instance;
    }

    private function createNewClassInstance($class) {
        $params = $this->getParams($class);
        if (empty($params)) {
            return new $class();
        }

        return new $class(...$params);
    }

    private function getParams($classPath)
    {
        if (!isset($this->constructorCache[$classPath])) {
            return $this->getParamOnFirstRun($classPath);
        }

        if (!$this->constructorCache[$classPath]) {
            return null;
        }

        $paramInstances = [];
        foreach ($this->constructorCache[$classPath] as $type) {
            $paramInstances[] = $this->getParamInstance($type);
        }

        return $paramInstances;
    }

    private function getParamOnFirstRun($classPath)
    {
        $reflectionClass = new ReflectionClass($classPath);
        $constructor = $reflectionClass->getConstructor();

        $this->implementsNewInstanceCache[$classPath] = !$reflectionClass->implementsInterface(NewInstance::class)
            && $reflectionClass->implementsInterface(SingleInstance::class);

        if ($constructor === null) {
            return $this->constructorCache[$classPath] = false;
        }

        $parameters = $constructor->getParameters();
        $this->constructorCache[$classPath] = [];
        $initialParamInstances = [];
        foreach ($parameters as $parameter) {
            $initialParamInstances[] = $this->getParamInstance($this->constructorCache[$classPath][] = $parameter->getType()->getName());
        }

        return $initialParamInstances;
    }

    private function getParamInstance($type)
    {
        return $this->getOverrideRules($type) ?: $this->getInstanceOf($type);
    }

    private function getOverrideRules($type)
    {
        return isset($this->overrideRules[$type]) ? $this->overrideRules[$type]() : null;
    }
}