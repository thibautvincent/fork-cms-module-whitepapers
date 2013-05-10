<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the elete-action, it delete an item
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersDelete extends BackendBaseActionDelete
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		$this->id = $this->getParameter('id', 'int');

		// does the item exist
		if($this->id !== null && BackendWhitepapersModel::exists($this->id))
		{
			parent::execute();
			$this->record = (array) BackendWhitepapersModel::get($this->id);

			// delete the whitepaper data
			BackendWhitepapersModel::delete($this->id);
			BackendSearchModel::removeIndex($this->getModule(), $this->id);

			BackendModel::triggerEvent($this->getModule(), 'after_delete', array('id' => $this->id));

			$this->redirect(BackendModel::createURLForAction('index') . '&report=deleted&var=' . urlencode($this->record['title']));
		}
		else $this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
	}
}
