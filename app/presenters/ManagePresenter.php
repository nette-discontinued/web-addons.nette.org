<?php

namespace NetteAddons;

use NetteAddons\Model\Users,
	NetteAddons\Model\AddonVersion,
	NetteAddons\Model\AddonVersions;



final class ManagePresenter extends Manage\BasePresenter
{
	/**
	 * Finish the addon creation
	 */
	public function actionFinish()
	{
		if ($this->addon === NULL) {
			$this->error();
		}

		try {
			$this->addons->add($this->addon);
			$this->manager->destroyAddon($this->getSessionKey());
			$this->flashMessage('Addon was successfully registered.');
			$this->redirect('Detail:', $this->addon->id);

		} catch (\NetteAddons\DuplicateEntryException $e) {
			$this->flashMessage("Adding new addon failed.", 'danger');
			$this->redirect(':Manage:Create:add');
		}
	}
}
