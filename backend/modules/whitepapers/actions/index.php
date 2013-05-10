<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the index-action (default), it will display the overview of whitepapers posts
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersIndex extends BackendBaseActionIndex
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadDataGrid();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the dataGrid
	 */
	protected function loadDataGrid()
	{
		$editUrl = BackendModel::createURLForAction('edit') . '&amp;id=[id]';

		$this->dataGrid = new BackendDataGridDB(BackendWhitepapersModel::QRY_BROWSE_WHITEPAPERS, array(BL::getWorkingLanguage()));
		$this->dataGrid->setSortingColumns(array('title', 'created_on', 'num_downloads'), 'num_downloads');
		$this->dataGrid->addColumn('edit', null, BL::lbl('Edit'), $editUrl, BL::lbl('Edit'));
		$this->dataGrid->setColumnURL('title', $editUrl);
		$this->dataGrid->setColumnURL('num_downloads', $editUrl . '#tabDownloads');
		$this->dataGrid->setColumnFunction(array('BackendDatagridFunctions', 'getLongDate'), array('[created_on]'), array('created_on'));
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// parse the dataGrid if there are results
		$this->tpl->assign('dataGrid', ($this->dataGrid->getNumResults() != 0) ? $this->dataGrid->getContent() : false);
	}
}
