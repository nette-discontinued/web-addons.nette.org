<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

/**
 * Access to method.
 *
 * <code>
 * $a = Access(new Object, 'methodName');
 * $a->call();
 * $a->call(1, 2, 3);
 * </code>
 *
 * <code>
 * $a = Access('Object', 'methodName');
 * $a->asInstance(new Object);
 * </code>
 */
class AccessMethod extends AccessBase
{

	/** @var AccessAccessor */
	private $access;

	/**
	 * @param object|string object or class name
	 * @param string method name
	 */
	public function __construct($object, $method)
	{
		parent::__construct($object, new ReflectionMethod($object, $method));
		$ac = PHP_VERSION_ID < 50302 ? 'AccessAccessorPhp52' : 'AccessAccessor';
		$accessor = new $ac;
		$this->access = $accessor->accessMethod($this->reflection);
	}

	/**
	 * @param mixed $params,...
	 * @return mixed
	 */
	public function call()
	{
		return $this->callArgs(func_get_args());
	}

	/**
	 * @param array
	 * @return mixed
	 */
	public function callArgs(array $args = array())
	{
		if (!$this->instance AND !$this->reflection->isStatic())
		{
			$c = $this->reflection->getDeclaringClass()->getName();
			$n = $this->reflection->getName();
			throw new Exception("Method $c::$n() is not static.");
		}
		return call_user_func($this->access, $this->instance, $args);
	}
}
