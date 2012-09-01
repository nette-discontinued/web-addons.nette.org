<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

require_once dirname(__FILE__) . '/Init.php';

abstract class AccessBase
{

	/** @var ReflectionClass|ReflectionMethod|ReflectionProperty */
	protected $reflection;

	/** @var object|NULL */
	protected $instance;

	/**
	 * @param object|string object or class name
	 * @param ReflectionClass|ReflectionMethod|ReflectionProperty
	 */
	public function __construct($object, Reflector $r)
	{
		$this->reflection = $r;
		if (is_object($object))
		{
			$this->instance = $object;
		}
	}

	/**
	 * @param object|NULL
	 * @return AccessBase $this
	 */
	public function asInstance($object)
	{
		if (is_object($object))
		{
			if ($this->reflection instanceof ReflectionClass)
			{
				$class = $this->reflection->getName();
			}
			else
			{
				$class = $this->reflection->getDeclaringClass()->getName();
			}
			if (!($object instanceof $class))
			{
				throw new Exception('Must be instance of accessible class.');
			}
		}
		else if ($object !== NULL)
		{
			throw new Exception('Instance must be object or NULL.');
		}
		$this->instance = $object;
		return $this;
	}

	/**
	 * Call to undefined method.
	 * @throws Exception
	 */
	public function __call($name, $args)
	{
		$class = get_class($this);
		throw new Exception("Call to undefined method $class::$name().");
	}

	/**
	 * Call to undefined static method.
	 * @throws Exception
	 */
	public static function __callStatic($name, $args)
	{
		$class = get_called_class();
		throw new Exception("Call undefined static method $class::$name().");
	}

	/**
	 * Read undeclared property.
	 * @throws Exception
	 */
	public function &__get($name)
	{
		$class = get_class($this);
		throw new Exception("Cannot read undeclared property $class::\$$name.");
	}

	/**
	 * Write to undeclared property.
	 * @throws Exception
	 */
	public function __set($name, $value)
	{
		$class = get_class($this);
		throw new Exception("Cannot write to undeclared property $class::\$$name.");
	}

	/**
	 * Access to undeclared property.
	 * @throws Exception
	 */
	public function __isset($name)
	{
		$class = get_class($this);
		throw new Exception("Cannot check existence of property $class::\$$name.");
	}

	/**
	 * Access to undeclared property.
	 * @throws Exception
	 */
	public function __unset($name)
	{
		$class = get_class($this);
		throw new Exception("Cannot unset property $class::\$$name.");
	}
}
