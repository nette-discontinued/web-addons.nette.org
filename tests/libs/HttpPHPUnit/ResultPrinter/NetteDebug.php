<?php

namespace HttpPHPUnit;

use Nette\Object;
use Exception;
use ReflectionProperty;

/**
 * @author Petr Prochazka
 */
class NetteDebug extends Object
{
	/** @var string */
	private $class;

	/** @var NetteDebug */
	private static $instance;

	/** @return NetteDebug */
	public static function get()
	{
		if (self::$instance === NULL)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __construct()
	{
		foreach (array(
			'Nette\Diagnostics\Debugger',
			'Nette\Debug',
			'Debugger',
			'Debug',
			'NDebugger',
			'NDebug',
		) as $class)
		{
			if (class_exists($class))
			{
				$this->class = $class;
				return;
			}
		}
		throw new Exception;
	}

	/**
	 * @param string
	 * @param array
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->class, $name), $args);
	}

	/**
	 * @param string
	 * @return mixed
	 */
	public function & __get($name)
	{
		$r = new ReflectionProperty($this->class, $name);
		$tmp = $r->getValue();
		return $tmp;
	}

	/**
	 * @param string
	 * @param mixed
	 * @return mixed
	 */
	public function __set($name, $value)
	{
		$r = new ReflectionProperty($this->class, $name);
		$r->setValue($value);
		return;
	}

	/**
	 * @param string
	 * @return bool
	 */
	public function __isset($name)
	{
		if (!property_exists($this->class, $name))
		{
			return false;
		}
		return $this->__get($name) !== NULL;
	}

}
