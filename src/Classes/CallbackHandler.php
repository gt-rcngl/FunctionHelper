<?php
/**
 * @author arcangel <jsh24.rcngl@gmail.com>
 * @package ArcangelWeb
 * @version 1.0.0
 * @link https://www.example.com
 */

namespace Arcangel\Functions\Helper;

use ReflectionFunction;
use ReflectionClass;
use ErrorException;

class CallbackHandler
{
    private $handler;
    private $method;


    public function __construct($callback, $method = null) {

        $this->handler = $callback;
        $this->method = $method;

        if (!$this->isCallable()) {
            $this->ClassHadlerMethod();
        } else {
            $this->CallableHandler();
        }

    }

    /*
    |-----------------------------------
    | Check if the handler is callable
    | Returns true if callable, otherwise false
    |-----------------------------------
    */
    public function isCallable() : bool {
        return (bool) is_callable($this->handler);
    }

    private $reflectionClass;

    /*
    |-----------------------------------
    | Instantiate a class
    | Returns an instance or reflection based on parameter
    |-----------------------------------
    */
    private function instantiateClass($reflection = false)
    {
        if (!class_exists($this->handler)) {
            throw new ErrorException("Class instantiation failed.");
        }

        $reflectionClass = new ReflectionClass($this->handler);

        if ($reflection) {
            return $reflectionClass;
        }

        if ($reflectionClass->isInstantiable()) {
            return $reflectionClass->newInstance();
        }

        throw new ErrorException("Class instantiation failed.");
    }

    private $callableMethodParameter ; 

    private function CallableHandler() 
    {
        $callableReflection = new ReflectionFunction($this->handler);

        $this->callableMethodParameter = $callableReflection->getParameters();
    }

    private $reflectionClassMethod;
    private $reflectionClassMethodParameter;

    private function ClassHadlerMethod() {
        $this->reflectionClass = $instamce = $this->instantiateClass();
        
        $reflection = new ReflectionClass($instamce);
        if (
            $reflection->hasMethod($this->method) && 
            $reflection->getMethod($this->method)->isPublic()
        ){

            $this->reflectionClassMethod = $classMethod = $reflection->getMethod($this->method);
            $this->reflectionClassMethodParameter = $classMethod->getParameters();
        } else {
            $string = "Undefined public method \"%s\" in class %s";
            throw new ErrorException(sprintf($string,(string) $this->method,$this->handler));
        }
    }

    /*
    |-----------------------------------
    | Call a function
    | Returns the result of the function call
    |-----------------------------------
    */
    private function callFunction($parameters = array()) {
        return call_user_func_array($this->handler, $parameters);
    }

    /*
    |-----------------------------------
    | Execute the callback
    | Returns the result of the executed callback
    |-----------------------------------
    */
    public function execute($params = []) : mixed {
        $param = [];
        $param = array_merge($param, $params);
        if ($this->isCallable()) {
            return $this->callFunction($param);
        } else {
            return $this
                ->reflectionClassMethod
                ->invokeArgs(
                    $this->reflectionClass, $param
                );
        }
    }

    /*
    |-----------------------------------
    | Get the handler
    | Returns the callback handler
    |-----------------------------------
    */
    public function getHadler()
    {
        return $this->handler;   
    }

    /*
    |-----------------------------------
    | Get the method
    | Returns the callback method
    |-----------------------------------
    */
    public function getMethod()
    {
        return $this->method;   
    }

    /*
    |-----------------------------------
    | Get the callback parameters
    | Returns the array of callback parameters
    |-----------------------------------
    */
    public function getCallbackParameters()
    {
        if ($this->isCallable()) {
            return $this->callableMethodParameter;
        }
        return $this->reflectionClassMethodParameter;   
    }

    /*
    |-----------------------------------
    | Check if the callback has parameters
    | Returns true if the callback has parameters, otherwise false
    |-----------------------------------
    */
    public function hasParameter() : bool {
        if ($this->isCallable()) {
            return (count($this->callableMethodParameter) > 0) ? true : false;
        }
        return (count($this->reflectionClassMethodParameter) > 0) ? true : false;
    }

    // reset method
    public function reIntantiateUsingMethod($method)
    {
        return new self(
            $this->handler, $method
        );
    }
}
