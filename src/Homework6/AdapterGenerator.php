<?php

namespace App\Homework6;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

class AdapterGenerator
{
    private IMethodParser $methodParser;
    private IDependencyNameComposer $depComposer;

    public function __construct(IMethodParser $methodParser, IDependencyNameComposer $depComposer)
    {
        $this->methodParser = $methodParser;
        $this->depComposer = $depComposer;
    }

    public function generateAdapter(string $interfaceName, UObject $object): object
    {
        if (!interface_exists($interfaceName)) {
            throw new InvalidArgumentException("Interface $interfaceName does not exist.");
        }

        $refClass = new ReflectionClass($interfaceName);
        $namespace = $refClass->getNamespaceName();
        $interfaceShortName = $refClass->getShortName();
        $className = 'Adapter' . $interfaceShortName;
        $methodsCode = '';

        foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $methodsCode .= $this->generateMethod($interfaceShortName, $method);
        }

        $classCode = <<<PHP
            namespace $namespace;
            use App\\Homework5\\IoC;
            use App\\Homework6\\UObject;
            class $className implements \\{$interfaceName} {
                private UObject \$adaptee;
                
                public function __construct(UObject \$adaptee) {
                    \$this->adaptee = \$adaptee;
                }

                $methodsCode
            }
        PHP;

        eval($classCode);

        $fullClassName = "$namespace\\$className";
        return new $fullClassName($object);
    }

    private function generateMethod(string $interfaceShortName, ReflectionMethod $method): string
    {
        $methodName = $method->getName();
        $parsedMethodName = $this->methodParser->parse($methodName);
        $dependencyName = $this->depComposer->compose($interfaceShortName, $parsedMethodName);
        $paramsCode = $this->generateMethodParameters($method);
        $paramsNames = $this->generateParameterNames($method);
        $paramsStr = empty($paramsNames) ? '' : ', ' . implode(', ', $paramsNames);
        $returnType = $this->getReturnType($method);

        if ($returnType === ': void') {
            return <<<PHP
                public function $methodName($paramsCode)$returnType {
                    \$dependency = IoC::Resolve('$dependencyName', \$this->adaptee$paramsStr);
                    \$dependency->execute();
                }
            PHP;
        }

        return <<<PHP
            public function $methodName($paramsCode)$returnType {
                return IoC::Resolve('$dependencyName', \$this->adaptee$paramsStr);
            }
        PHP;
    }

    private function generateParameterNames(ReflectionMethod $method): array
    {
        $params = [];

        foreach ($method->getParameters() as $param) {
            $params[] = '$' . $param->getName();
        }

        return $params;
    }

    private function generateMethodParameters(ReflectionMethod $method): string
    {
        $params = [];

        foreach ($method->getParameters() as $param) {
            $paramCode = $this->getParameterType($param) . '$' . $param->getName();

            if ($param->isDefaultValueAvailable()) {
                $default = var_export($param->getDefaultValue(), true);
                $paramCode .= " = $default";
            }

            $params[] = $paramCode;
        }

        return implode(', ', $params);
    }

    private function getReturnType(ReflectionMethod $method): string
    {
        $type = $method->getReturnType();
        if (!$type) {
            return '';
        }

        $typeStr = $this->getTypeString($type);
        return ": $typeStr";
    }

    private function getParameterType(ReflectionParameter $param): string
    {
        $type = $param->getType();
        return $type ? $this->getTypeString($type) . ' ' : '';
    }

    private function getTypeString(ReflectionNamedType $type): string
    {
        $typeStr = $type->getName();
        if (!$type->isBuiltin()) {
            $typeStr = '\\' . $typeStr;
        }

        return $type->allowsNull() ? "?$typeStr" : $typeStr;
    }
}
