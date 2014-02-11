<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

/**
 * Access to whole class.
 *
 * <code>
 * $a = Access(new Object);
 *
 * $a->method();
 * $a->method(1, 2, 3);
 *
 * $a->property = 123;
 * $a->property;
 * </code>
 *
 * <code>
 * $a = Access('Object');
 * $a->asInstance(new Object);
 * </code>
 */
class AccessClass extends AccessBase
{
	/** @var array of AccessMethod */
	private $methods = array();

	/** @var array of AccessProperty */
	private $properties = array();

	/**
	 * @param object|string object or class name
	 */
	public function __construct($object)
	{
		parent::__construct($object, new ReflectionClass($object));
	}

	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (!isset($this->methods[$name]))
		{
			$a = new AccessMethod($this->reflection->getName(), $name);
			$this->methods[$name] = $a->asInstance($this->instance);
		}
		return $this->methods[$name]->callArgs($args);
	}

	/**
	 * @param string
	 * @return mixed
	 */
	public function & __get($name)
	{
		if (!isset($this->properties[$name]))
		{
			$a = new AccessProperty($this->reflection->getName(), $name);
			$this->properties[$name] = $a->asInstance($this->instance);
		}
		$tmp = $this->properties[$name]->get($name);
		return $tmp;
	}

	/**
	 * @param string
	 * @param mixed
	 * @return void
	 */
	public function __set($name, $value)
	{
		if (!isset($this->properties[$name]))
		{
			$a = new AccessProperty($this->reflection->getName(), $name);
			$this->properties[$name] = $a->asInstance($this->instance);
		}
		$this->properties[$name]->set($value);
	}

	/**
	 * @param object|NULL
	 * @return AccessClass $this
	 */
	public function asInstance($object)
	{
		parent::asInstance($object);
		foreach ($this->methods as $a)
		{
			$a->asInstance($this->instance);
		}
		foreach ($this->properties as $a)
		{
			$a->asInstance($this->instance);
		}
		return $this;
	}
}
