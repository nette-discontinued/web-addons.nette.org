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
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	public function isAllowed($resource, $action)
	{
		/*
		if ($resource instanceof Nette\Database\Table\ActiveRow) {
			$resource = $this->fromActiveRow($resource);
		}

		if ($resource instanceof Addon) {
			if ($action === 'view') {
				return TRUE;

			} elseif ($action === 'manage') {
				return (
					($this->user->isLoggedIn() && $resource->userId === $this->user->getIdentity()->id) ||
					$this->user->isInRole('moderator')
				);

			} elseif ($action === 'vote') {
				// you can't vote for your own addons
				return ($this->user->isLoggedIn() && $resource->userId !== $this->user->getIdentity()->id);
			}

		} elseif ($resource === 'addon') {
			if ($action === 'create') {
				return $this->user->isLoggedIn();
			}
		}

		throw new \NetteAddons\InvalidArgumentException();
		*/
		return TRUE;
	}

	/**
	 * Convers ActiveRow to entity
	 *
	 * @param  Nette\Database\Table\ActiveRow
	 * @return mixed
	 * @throws \NetteAddons\InvalidArgumentException
	 */
	private function fromActiveRow(Nette\Database\Table\ActiveRow $row)
	{
		if ($row->getTable()->getName() === 'addon') {
			return Addon::fromActiveRow($row);
		}

		throw new \NetteAddons\InvalidArgumentException();
	}

}
