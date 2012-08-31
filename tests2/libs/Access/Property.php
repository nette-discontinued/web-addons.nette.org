<?php
/**
 * Access
 * @link http://github.com/PetrP/Access
 * @author Petr Procházka (petr@petrp.cz)
 * @license "New" BSD License
 */

/**
 * Access to property.
 *
 * <code>
 * $a = Access(new Object, '$propertyName');
 * $a->set(123);
 * $a->get(123);
 * </code>
 *
 * <code>
 * $a = Access('Object', '$propertyName');
 * $a->asInstance(new Object);
 * </code>
 */
class AccessProperty extends AccessBase
{

	/** @var AccessAccessor */
	private $access;

	/**
	 * @param object|string object or class name
	 * @param string property name
	 */
	public function __construct($object, $property)
	{
		try {
			$r = new ReflectionProperty($object, $property);
		} catch (ReflectionException $e) {
			$class = $object;
			while ($class = get_parent_class($class))
			{
				try {
					$r = new ReflectionProperty($class, $property);
					break;
				} catch (ReflectionException $ee) {}
			}
			if (!isset($r)) throw $e;
		}
		parent::__construct($object, $r);
		$ac = PHP_VERSION_ID < 50300 ? 'AccessAccessorPhp52' : 'AccessAccessor';
		$accessor = new $ac;
		$this->access = $accessor->accessProperty($this->reflection);
	}

	/** @return mixed */
	public function get()
	{
		$this->check();
		return call_user_func($this->access->get, $this->instance);
	}

	/**
	 * @param mixed
	 * @return AccessProperty $this
	 */
	public function set($value)
	{
		$this->check();
		call_user_func($this->access->set, $this->instance, $value);
		return $this;
	}

	private function check()
	{
		if (!$this->instance AND !$this->reflection->isStatic())
		{
			$c = $this->reflection->getDeclaringClass()->getName();
			$n = $this->reflection->getName();
			throw new Exception("Property $c::$$n is not static.");
		}
	}
}
