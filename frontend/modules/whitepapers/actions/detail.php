<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the Detail-action, it will display the overview of whitepapers posts
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class FrontendWhitepapersDetail extends FrontendBaseBlock
{
	/**
	 * The download url
	 *
	 * @var string
	 */
	private $downloadUrl;

	/**
	 * The form instance
	 *
	 * @var FrontendForm
	 */
	private $form;

	/**
	 * A check if the newsletter module is installed or not
	 *
	 * @var bool
	 */
	private $isNewsletterInstalled = false;

	/**
	 * The record data
	 *
	 * @var	array
	 */
	private $record;

	/**
	 * Show the data form or not?
	 *
	 * @var bool
	 */
	private $showDataForm = false, $submitAfterDownload = true;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadTemplate();
		$this->loadDownloadForm();
		$this->loadData();

		$this->validateDownloadForm();

		$this->parse();
	}

	/**
	 * Load the data
	 */
	protected function loadData()
	{
		$whitepaperUrl = ($this->URL->getParameter(1) === null) ? $this->URL->getParameter(0) : $this->URL->getParameter(1);
		$whitepaperId = ($whitepaperUrl === null) ? 0 : FrontendWhitepapersModel::getIdForUrl($whitepaperUrl);
		if($whitepaperId == 0) $this->redirect(FrontendNavigation::getURL(404));

		$this->record = FrontendWhitepapersModel::getDataForId($whitepaperId);
		$this->isNewsletterInstalled = (array_search('mailmotor', FrontendModel::getModules()) != false) ? true : false;
		$this->downloadUrl = SITE_URL . FrontendNavigation::getURLForBlock('whitepapers', 'download') . '/' . $this->record['url'];
		$this->submitAfterDownload = FrontendModel::getModuleSetting('whitepapers', 'submit_after_download', true);

		$startDownload = $this->URL->getParameter('download', 'bool', false);
		$dataSubmitted = $this->URL->getParameter('submitted', 'bool', false);

		// we should start de download before the submit form
		if($startDownload && $this->submitAfterDownload && !$this->form->isSubmitted())
		{
			$this->storeBrowserData();
		}
		if(($startDownload && $this->submitAfterDownload) || $dataSubmitted || !$this->submitAfterDownload) $this->showDataForm = true;

		$isSent = $this->URL->getParameter('sent', 'bool', false);
		if($isSent && SpoonSession::get('download-' . $this->record['id']))
		{
			// add a download to the whitepaper
			FrontendWhitepapersModel::addDownload($this->record['id']);

			// assign the header so we can download the file
			$this->header->addMetaData(array(
				'http-equiv' => 'refresh',
				'content' => '2;url=' . $this->downloadUrl
			));
		}
	}

	/**
	 * This will load the download form
	 */
	protected function loadDownloadForm()
	{
		$cookieData = SpoonCookie::get('whitepaper_data');
		$cookieData = ($cookieData !== false) ? unserialize($cookieData) : array();
		$name = (isset($cookieData['name'])) ? $cookieData['name'] : null;
		$email = (isset($cookieData['email'])) ? $cookieData['email'] : null;
		$phone = (isset($cookieData['phone'])) ? $cookieData['phone'] : null;

		// create the form and its elements
		$this->form = new FrontendForm('download');
		$this->form->addText('name', $name);
		$this->form->addText('email', $email);
		$this->form->addText('phone', $phone);
		$this->form->addCheckbox('newsletter', false);
	}

	/**
	 * Log that there was an error
	 *
	 * @param string $errorMessage
	 */
	protected function logError($errorMessage)
	{
		// create the log
		$log = new SpoonLog();
		$log->setPath(FRONTEND_FILES_PATH . '/whitepapers');
		$log->setType('error_log');

		$log->write($errorMessage);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		// assign the variables
		$this->tpl->assign('whitepaper', $this->record);
		$this->tpl->assign('hideContentTitle', true);
		$this->tpl->assign('newsletter', $this->isNewsletterInstalled);
		$this->tpl->assign('showDataForm', $this->showDataForm);
		$this->tpl->assign('submitAfterDownload', $this->submitAfterDownload);

		// build Facebook Open Graph-data
		if(FrontendModel::getModuleSetting('core', 'facebook_admin_ids', null) !== null || FrontendModel::getModuleSetting('core', 'facebook_app_id', null) !== null)
		{
			// add specified image
			$this->header->addOpenGraphImage(FRONTEND_FILES_URL . '/whitepapers/images/source/' . $this->record['image']);

			// add images from content
			$this->header->extractOpenGraphImages($this->record['text']);

			// add additional OpenGraph data
			$this->header->addOpenGraphData('title', $this->record['title'], true);
			$this->header->addOpenGraphData('type', 'article', true);
			$this->header->addOpenGraphData('url', SITE_URL . $this->record['full_url'], true);
			$this->header->addOpenGraphData('site_name', FrontendModel::getModuleSetting('core', 'site_title_' . FRONTEND_LANGUAGE, SITE_DEFAULT_TITLE), true);
			$this->header->addOpenGraphData('description', $this->record['title'], true);
		}

		// the meta
		$this->header->setPageTitle($this->record['meta']['title'], ($this->record['meta']['title_overwrite'] == 'Y'));
		$this->header->addMetaDescription($this->record['meta']['description'], ($this->record['meta']['description_overwrite'] == 'Y'));
		$this->header->addMetaKeywords($this->record['meta']['keywords'], ($this->record['meta']['title_overwrite'] == 'Y'));

		// breadcrump data
		$this->breadcrumb->addElement($this->record['title'], $this->record['full_url']);

		$errorMessage = $this->URL->getParameter('error', 'string', null);
		$isSubmitted = $this->URL->getParameter('submitted', 'bool', false);
		if(SpoonSession::get('download-' . $this->record['id']) || $errorMessage !== null || ($this->showDataForm && $this->submitAfterDownload))
		{
			// set the class so we can display nice colors
			$downloadMessage = false;
			$messageClass = 'message success';

			// there was an error downloading the file
			if($errorMessage !== null)
			{
				// display that something went wrong
				$downloadMessage = FL::err('FileDownloadFailed');
				$messageClass = 'message error';

				// log the error
				$this->logError($errorMessage);
			}

			// this form is filled after downloading
			elseif($this->showDataForm && $this->submitAfterDownload)
			{
				// the form is filled
				if($isSubmitted) $downloadMessage = FL::msg('InformationSubmitted');
				// show a message that we've downloaded the whitepaper
				else $this->tpl->assign('submitMessage', sprintf(FL::msg('WhitepaperStartDownloaded'), $this->downloadUrl));
			}

			// the whitepaper is downloaded
			else $downloadMessage = sprintf(FL::msg('WhitepaperDownloaded'), $this->downloadUrl);

			// assign the message class
			$this->tpl->assign('downloadMessage', $downloadMessage);
			$this->tpl->assign('messageClass', $messageClass);
		}

		// parse the form
		if($this->showDataForm) $this->form->parse($this->tpl);
	}

	/**
	 * This will store some data in the browser for later usage
	 *
	 * @param array[optional] $data The data that will be stored in the browser
	 * @param bool[optional] $setSession Should we set a session so we can download the file?
	 */
	protected function storeBrowserData(array $data = array(), $setSession = true)
	{
		// store that we've downloaded the whitepaper
		SpoonSession::set('download-' . $this->record['id'], $setSession);

		// the data to store in the cookies
		$cookieData = array();
		foreach($data as $downloadValue) $cookieData[$downloadValue['name']] = $downloadValue['value'];

		// store the form data so we can use this in other forms
		SpoonCookie::set('whitepaper_data', serialize($cookieData));
	}

	/**
	 * This will validate the form and parse the errors if needed. If the form is correct it will
	 * force download the whitepaper.
	 */
	protected function validateDownloadForm()
	{
		if($this->form->isSubmitted())
		{
			$this->form->cleanupFields();

			// form validation
			$fields = $this->form->getFields();
			$fields['name']->isFilled(FL::err('FieldIsRequired'));
			$fields['email']->isEmail(FL::err('EmailIsInvalid'));

			if($this->form->isCorrect())
			{
				// should we subscribe this person to the newsletter or not?
				if($this->isNewsletterInstalled)
				{
					if($fields['newsletter']->getChecked()) FrontendMailmotorModel::subscribe($fields['email']->getValue());
				}

				// save the information provided by the user
				$downloadData = FrontendWhitepapersModel::saveDownloadData($this->record['id'], $fields);

				// trigger an event to show that we've downloaded the whitepaper
				FrontendModel::triggerEvent($this->getModule(), 'after_download', serialize($downloadData));

				// store some information in the browser
				$this->storeBrowserData($downloadData, !$this->submitAfterDownload);

				$this->redirect(FrontendNavigation::getURLForBlock('whitepapers', 'detail') . '/' . $this->record['url'] . '?sent=true&submitted=true');
			}
		}
	}
}
