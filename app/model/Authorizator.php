<?php

namespace NetteAddons\Model;

use Nette;



/**
 * @author Jan Tvrdík
 */
class Authorizator extends Nette\Object
{

	/** @var \Nette\Security\User */
	private $user;

	public function __construct(\Nette\Security\User $user)
	{
		$this->user = $user;
	}

	/**
	 * Is user allowed to perform given action with given resource.
	 *
	 * @param  mixed
	 * @param  string for example 'view', 'edit'
	 * @return bool
	 */
	public function isAllowed($resource, $action)
	{
		if ($resource instanceof Addon) {
			if ($action === 'view') {
				return TRUE;

			} elseif ($action === 'edit') {
				return (
					$resource->user === $this->user->getIdentity() ||
					$this->user->isInRole('moderator')
				);
			}

		} elseif ($resource === 'addon') {
			if ($action === 'create') {
				return $this->user->isLoggedIn();
			}
		}

		throw new \NetteAddons\InvalidArgumentException();
	}

}
