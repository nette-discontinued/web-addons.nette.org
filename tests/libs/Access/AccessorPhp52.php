<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

/**
 * Accessor for PHP 5.2.
 *
 * Uses abstract static subclass to access to protected members (created via eval).
 * And object-to-array conversion to read private properties.
 *
 * Not supported:
 * 	- Final classes.
 * 	- Private methods.
 * 	- Read private static property.
 * 	- Write private property.
 *
 * @see AccessAccessor
 */
class AccessAccessorPhp52 extends AccessBase
{

	/** @var array className => helperClassName */
	private static $helperClasses = array();

	/** @var array @access private */
	static $callbackUses = array();

	public function __construct()
	{

	}

	/**
	 * @param ReflectionMethod
	 * @return callable(object|NULL $instance, array $args)
	 */
	public function accessMethod(ReflectionMethod $method)
	{
		if (PHP_VERSION_ID >= 50302 OR $method->isPublic())
		{
			if (PHP_VERSION_ID >= 50302)
			{
				$method->setAccessible(true);
			}
			return $this->callback('$instance, array $args', '
				return call_user_func_array(array($method, "invoke"), array_merge(array($instance), $args));
			', array('method' => $method));
		}
		else if ($method->isProtected())
		{
			return $this->callback('$instance, array $args', '
				if (!$instance) $instance = $className; // static
				return call_user_func(array($helperClassName, "__AccessAccessor_php52__invoke"), $instance, $methodName, $args);
			', array(
				'helperClassName' => $this->getHelperClass($method->getDeclaringClass()),
				'className' => $method->getDeclaringClass()->getName(),
				'methodName' => $method->getName(),
			));
		}
		else if ($method->isPrivate())
		{
			throw new Exception('AccessMethod needs PHP 5.3.2 or newer to call private method.');
		}
	}

	/**
	 * @param ReflectionProperty
	 * @return object get => callable(object|NULL $instance), set => callable(object|NULL $instance, mixed $value)
	 */
	public function accessProperty(ReflectionProperty $property)
	{
		if (PHP_VERSION_ID >= 50300 OR $property->isPublic())
		{
			if (PHP_VERSION_ID >= 50300)
			{
				$property->setAccessible(true);
			}
			return (object) array(
				'get' => $this->callback('$instance', '
					if ($instance)
					{
						return $property->getValue($instance);
					}
					return $property->getValue();
				', array('property' => $property)),
				'set' => $this->callback('$instance, $value', '
					if ($instance)
					{
						$property->setValue($instance, $value);
					}
					else
					{
						$property->setValue($value);
					}
				', array('property' => $property)),
			);
		}
		else if ($property->isProtected())
		{
			return (object) array(
				'get' => $this->callback('$instance', '
					if ($instance AND $property->isStatic()) $instance = NULL;
					return call_user_func(array($helperClassName, "__AccessAccessor_php52__get"), $instance, $propertyName);
				', array(
					'property' => $property,
					'helperClassName' => $this->getHelperClass($property->getDeclaringClass()),
					'propertyName' => $property->getName(),
				)),
				'set' => $this->callback('$instance, $value', '
					if ($instance AND $property->isStatic()) $instance = NULL;
					return call_user_func(array($helperClassName, "__AccessAccessor_php52__set"), $instance, $propertyName, $value);
				', array(
					'property' => $property,
					'helperClassName' => $this->getHelperClass($property->getDeclaringClass()),
					'propertyName' => $property->getName(),
				)),
			);
		}
		else if ($property->isPrivate())
		{
			if ($property->isStatic())
			{
				throw new Exception('AccessProperty needs PHP 5.3.0 or newer to access static private property.');
			}
			return (object) array(
				'get' => $this->callback('$instance', '
					if ($instance)
					{
						$array = (array) $instance;
						return $array["\0{$className}\0{$propertyName}"];
					}
					throw new Exception("AccessProperty needs PHP 5.3.0 or newer to access static private property.");
				', array(
					'helperClassName' => $this->getHelperClass($property->getDeclaringClass()),
					'className' => $property->getDeclaringClass()->getName(),
					'propertyName' => $property->getName(),
				)),
				'set' => $this->callback('$instance, $value', '
					throw new Exception("AccessProperty needs PHP 5.3.0 or newer to write to private property.");
				'),
			);
		}
	}

	/**
	 * Dynamically creates helper subclass.
	 *
	 * @param ReflectionClass
	 * @return string helper class name
	 */
	protected function getHelperClass(ReflectionClass $class)
	{
		$className = $class->getName();
		if (!isset(self::$helperClasses[$className]))
		{
			if ($class->isFinal())
			{
				throw new Exception('Access needs PHP 5.3 to work with final classes.');
			}

			$helperClassName = $className . '__AccessAccessor_php52__' . md5(lcg_value());
			eval("
				abstract class {$helperClassName} extends {$className}
				{
					static function __AccessAccessor_php52__invoke(\$object, \$method, \$arguments)
					{
						return call_user_func_array(array(\$object, \$method), \$arguments);
					}
					static function __AccessAccessor_php52__get(\$object, \$property)
					{
						if (\$object)
						{
							return \$object->{\$property};
						}
						return {$className}::\${\$property};
					}
					static function __AccessAccessor_php52__set(\$object, \$property, \$value)
					{
						if (\$object)
						{
							\$object->{\$property} = \$value;
						}
						else
						{
							{$className}::\${\$property} = \$value;
						}
					}
				}
			");
			self::$helperClasses[$className] = $helperClassName;
		}
		return self::$helperClasses[$className];
	}

	/**
	 * PHP 5.2 pseudo closure with `use` statement support.
	 *
	 * @param string `$a, $b`
	 * @param string php code
	 * @param array variableName => mixed
	 * @return callable
	 */
	protected function callback($parameters, $body, array $uses = array())
	{
		self::$callbackUses[] = $uses;
		return create_function($parameters, '
			extract(AccessAccessorPhp52::$callbackUses[' . (count(self::$callbackUses) - 1) . '], EXTR_REFS);
		' . $body);
	}

}
