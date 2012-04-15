<?php
/**
 * Useful functions and shortcuts
 * ------------------------------
 *
 * Functions are intentionally registered in global namespace because if they would be registered for example
 * in "NetteAddons" namespace they would be unavailable in "Skeleton\Foo" namespace.
 */

use Nette\Diagnostics\Debugger;



/**
 * PHP workaround for direct usage of created class
 *
 * <code>
 *  // echo (new Person)->name; // does not work in PHP
 *  echo c(new Person)->name;
 * </code>
 *
 * @author   Jan Tvrdík
 * @param    object
 * @return   object
 */
function c($instance)
{
	return $instance;
}

/**
 * PHP workaround for direct usage of cloned instances
 *
 * <code>
 *  echo cl($startTime)->modify('+1 day')->format('Y-m-d');
 * </code>
 *
 * @author   Jan Tvrdík
 * @param    object
 * @return   object
 */
function cl($instance)
{
	return clone $instance;
}

/**
 * Shortcut for Debugger::dump
 *
 * @author   Jan Tvrdík
 * @param    mixed
 * @param    mixed $var, ...       optional additional variable(s) to dump
 * @return   mixed                 the first dumped variable
 */
function d($var)
{
	foreach (func_get_args() as $var) Debugger::dump($var);
	return func_get_arg(0);
}

/**
 * Shortcut for Debugger::barDump
 *
 * @author   Jan Tvrdík
 * @param    mixed
 * @param    mixed $var, ...       optional additional variable(s) to dump
 * @return   mixed                 the first dumped variable
 */
function bd($var, $title = NULL)
{
	return Debugger::barDump($var, $title);
}

/**
 * Shortcut for Debugger::dump & exit()
 *
 * @author   Jan Tvrdík
 * @param    mixed
 * @param    mixed $var, ...       optional additional variable(s) to dump
 * @return   void
 */
function de($var)
{
	foreach (func_get_args() as $var) Debugger::dump($var);
	exit;
}
