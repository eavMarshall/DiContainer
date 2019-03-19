<?php
namespace Di;

use Exception;
use ReflectionClass;
use ReflectionParameter;

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
    private $reflectionParameterCache = [];
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
            return isset($this->singeInstancesCache[$class])
                ? $this->singeInstancesCache[$class]
                : $this->singeInstancesCache[$class] = new $class(...$this->getParams($class));
        }

        return new $class(...$this->getParams($class));
    }

    /**
     * @deprecated
     * I dont' want to do this, but php 5.6 doesn't support getting the type hint, so I'm
     * force to infer the type via the __toString() method.
     * When we finally move to php7 we can swap this out for ReflectionParameter::getType()
     * @param ReflectionParameter $parameter
     * @return mixed
     * @throws Exception
     */
    private function getTypeForPHP5_6(ReflectionParameter $parameter)
    {
        $parameterString = $parameter->__toString();
        if (isset($this->reflectionParameterCache[$parameterString])) {
            return $this->reflectionParameterCache[$parameterString];
        }

        $matches = [];
        preg_match_all("/\[([^\]]*)\]/", $parameterString, $matches);
        $matches = explode(' ', trim($matches[1][0]));

        if (count($matches) === 3) {
            return $this->reflectionParameterCache[$parameterString] = $matches[1];
        }

        throw new Exception("Unable to find type from ReflectionParameter $parameterString");
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
        $parameters = (new ReflectionClass($classPath))->getConstructor()->getParameters() ?: [];
        $this->constructorCache[$classPath] = [];
        $initialParamInstances = [];
        foreach ($parameters as $parameter) {
            $initialParamInstances[] = $this->getParamInstance($this->constructorCache[$classPath][] = $this->getTypeForPHP5_6($parameter));
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