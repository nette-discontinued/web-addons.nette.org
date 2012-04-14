<?php

namespace NetteAddons;


final class ManagePresenter extends BasePresenter
{
	public function actionAdd()
	{

	}

	protected function createComponentAddAddonForm()
	{
		$form = new AddAddonForm();
		return $form;
	}
}
