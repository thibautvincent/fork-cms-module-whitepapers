<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is a widget
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class FrontendWhitepapersWidgetRecentWhitepapers extends FrontendBaseWidget
{
	/**
	 * @var	array
	 */
	private $record;

	/**
	 * Exceute the action
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
	private function loadData()
	{
		$recentWhitepapersLimit = FrontendModel::getModuleSetting($this->getModule(), 'recent_whitepapers_num_items');
		$this->record = FrontendWhitepapersModel::getAllForRange($recentWhitepapersLimit);
	}

	/**
	 * Parse the widget
	 */
	protected function parse()
	{
		$this->tpl->assign('widgetRecentWhitepapers', $this->record);
	}
}
