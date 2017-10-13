<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the settings-action, it will display a form to manage the module settings
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersSettings extends BackendBaseActionEdit
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadForm();
		$this->validateForm();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		// create form
		$this->frm = new BackendForm('settings');

		// add fields for pagination
		$this->frm->addDropdown('overview_number_of_items', array_combine(range(1, 30), range(1, 30)), BackendModel::getModuleSetting($this->URL->getModule(), 'overview_num_items', 10));
		$this->frm->addDropdown('recent_whitepapers_number_of_items', array_combine(range(1, 10), range(1, 10)), BackendModel::getModuleSetting($this->URL->getModule(), 'recent_whitepapers_num_items', 5));
		$this->frm->addDropdown('related_whitepapers_number_of_items', array_combine(range(1, 10), range(1, 10)), BackendModel::getModuleSetting($this->URL->getModule(), 'related_whitepapers_num_items', 5));
		$this->frm->addCheckbox('submit_after_download', BackendModel::getModuleSetting($this->URL->getModule(), 'submit_after_download', true));
		$this->frm->addCheckbox('send_in_mail', BackendModel::getModuleSetting($this->URL->getModule(), 'send_in_mail', false));
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			if($this->frm->isCorrect())
			{
				// save the settings
				BackendModel::setModuleSetting($this->URL->getModule(), 'overview_num_items', (int) $this->frm->getField('overview_number_of_items')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'recent_whitepapers_num_items', (int) $this->frm->getField('recent_whitepapers_number_of_items')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'related_whitepapers_num_items', (int) $this->frm->getField('related_whitepapers_number_of_items')->getValue());
				BackendModel::setModuleSetting($this->URL->getModule(), 'submit_after_download', (bool) $this->frm->getField('submit_after_download')->getChecked());
				BackendModel::setModuleSetting($this->URL->getModule(), 'send_in_mail', (bool) $this->frm->getField('send_in_mail')->getChecked());

				BackendModel::triggerEvent($this->getModule(), 'after_saved_settings');
				$this->redirect(BackendModel::createURLForAction('settings') . '&report=saved');
			}
		}
	}
}
