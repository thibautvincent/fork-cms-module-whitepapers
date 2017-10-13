<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the Index-action, it will display the overview of whitepapers posts
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class FrontendWhitepapersIndex extends FrontendBaseBlock
{
	/**
	 * The record data
	 *
	 * @var	array
	 */
	private $record;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();
		$this->loadTemplate();
		$this->loadData();
		$this->parse();
	}

	/**
	 * Load the data
	 */
	protected function loadData()
	{
		// requested page
		$requestedPage = $this->URL->getParameter('page', 'int', 1);

		// set URL and limit
		$this->pagination['url'] = FrontendNavigation::getURLForBlock('whitepapers');
		$this->pagination['limit'] = FrontendModel::getModuleSetting('whitepapers', 'overview_num_items', 10);

		// populate count fields in pagination
		$this->pagination['num_items'] = FrontendWhitepapersModel::getAllCount();
		$this->pagination['num_pages'] = (int) ceil($this->pagination['num_items'] / $this->pagination['limit']);

		// num pages is always equal to at least 1
		if($this->pagination['num_pages'] == 0) $this->pagination['num_pages'] = 1;

		// redirect if the request page doesn't exist
		if($requestedPage > $this->pagination['num_pages'] || $requestedPage < 1) $this->redirect(FrontendNavigation::getURL(404));

		// populate calculated fields in pagination
		$this->pagination['requested_page'] = $requestedPage;
		$this->pagination['offset'] = ($this->pagination['requested_page'] * $this->pagination['limit']) - $this->pagination['limit'];

		$this->record = FrontendWhitepapersModel::getAllForRange($this->pagination['limit'], $this->pagination['offset']);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		$this->tpl->assign('items', $this->record);
		$this->parsePagination();
	}
}
