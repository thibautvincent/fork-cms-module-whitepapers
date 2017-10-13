<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Installer for the whitepapers module
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class WhitepapersInstaller extends ModuleInstaller
{
	public function install()
	{
		// import the sql
 		$this->importSQL(dirname(__FILE__) . '/data/install.sql');

		// install the module in the database
		$this->addModule('whitepapers', 'The whitepapers module.');

		// install the locale, this is set here beceause we need the module for this
		$this->importLocale(dirname(__FILE__) . '/data/locale.xml');

		$this->setModuleRights(1, 'whitepapers');

		$this->setActionRights(1, 'whitepapers', 'add');
		$this->setActionRights(1, 'whitepapers', 'edit');
		$this->setActionRights(1, 'whitepapers', 'delete');
		$this->setActionRights(1, 'whitepapers', 'download');
		$this->setActionRights(1, 'whitepapers', 'settings');
		$this->setActionRights(1, 'whitepapers', 'index');

		// add extra's
		$this->insertExtra('whitepapers', 'widget', 'RecentWhitepapers', 'recent_whitepapers');
		$whitepapersID = $this->insertExtra('whitepapers', 'block', 'Whitepapers', null, null, 'N', 1000);

		$navigationModulesId = $this->setNavigation(null, 'Modules');
		$navigationWhitepapersId = $this->setNavigation($navigationModulesId, 'Whitepapers', 'whitepapers/index', array('whitepapers/add', 'whitepapers/edit'));

		$navigationSettingsId = $this->setNavigation(null, 'Settings');
		$navigationModulesId = $this->setNavigation($navigationSettingsId, 'Modules');
		$this->setNavigation($navigationModulesId, 'Whitepapers', 'whitepapers/settings');
	}
}
