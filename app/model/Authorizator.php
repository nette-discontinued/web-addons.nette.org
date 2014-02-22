<?php

namespace NetteAddons\Model;


class Authorizator extends \Nette\Object
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
	 * @param mixed
	 * @param string for example 'view', 'edit'
	 * @return bool
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function isAllowed($resource, $action)
	{
		$moderator = $this->user->isInRole('administrators') || $this->user->isInRole('moderators');

		if ($resource instanceof Addon) {
			$ownerId = $resource->userId;
			$resource = 'addon';

		} elseif ($resource instanceof \Nette\Database\Table\ActiveRow) {
			$ownerId = $resource->user->id;
			$resource = 'addon';

		} elseif ($resource == 'page' && $action == 'manage') {
			return $moderator;

		} elseif ($resource != 'addon') {
			throw new \NetteAddons\InvalidArgumentException();
		}

		if ($resource === 'addon') {
			if ($action === 'delete' || $action === 'reports') {
				return $moderator;
			}
			if ($action === 'view') {
				return TRUE;

			} elseif ($action === 'manage') {
				return (
					($this->user->isLoggedIn() && $ownerId === $this->user->getId()) ||
					$moderator
				);

			} elseif ($action === 'vote') {
				// you can't vote for your own addons
				return ($this->user->isLoggedIn() && $ownerId !== $this->user->getId());

			} elseif ($action === 'create') {
				return $this->user->isLoggedIn();
			}
		}

		throw new \NetteAddons\InvalidArgumentException();
	}
}
