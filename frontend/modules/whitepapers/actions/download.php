<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the Download-action, it will display the overview of whitepapers posts
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class FrontendWhitepapersDownload extends FrontendBaseBlock
{
	/**
	 * The filepath
	 *
	 * @var string
	 */
	private $filePath;

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
		$this->loadData();

		// check if the user is allowed to download this whitepaper
		if($this->isUserAllowedDownload())
		{
			// check if the file exists, this should always be true but better safe than sorry.
			$this->filePath = FRONTEND_FILES_PATH . '/whitepapers/files/' . $this->record['filename'];
			if(SpoonFile::exists($this->filePath))
			{
				// should we send this in an email or force the download?
				$sendInMail = FrontendModel::getModuleSetting($this->module, 'send_in_mail');

				if(!$sendInMail) $this->forceDownload();
				else $this->sendEmail();
			}
			// for some reason, the file does not exist, return to the whitepaper page
			else
			{
				$this->redirect(FrontendNavigation::getURLForBlock('whitepapers', 'detail') . '/' . $this->record['url'] . '?error=file-not-found');
			}
		}
		else $this->redirect(FrontendNavigation::getURL(404));
	}

	/**
	 * This will force the page to download the whitepaper file
	 */
	protected function forceDownload()
	{
		// get the file extension
		$fileExtension = strrchr($this->record['filename'], '.');

		// add the extensions and content types if needed
		switch($fileExtension)
		{
			case '.pdf':
			default:
				$contentType = 'application/pdf';
				break;
		}

		// set the headers and download the file
		SpoonHTTP::setHeaders(array(
			'Content-Disposition: attachment; filename="' . $this->record['filename'] . '"',
			'Content-Type: ' . $contentType,
			'Content-Length: ' . filesize($this->filePath)
		));
		echo SpoonFile::getContent($this->filePath);
		exit;
	}

	/**
	 * Check if the user is allowed to download this specific whitepaper.
	 *
	 * This has a individual function so you could update this easily with checks for the profiles
	 * module.
	 */
	protected function isUserAllowedDownload()
	{
		$isAllowed = SpoonSession::get('download-' . $this->record['id']);

		// you can add some extra validation here, for example for the profiles module

		return $isAllowed;
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
	}

	/**
	 * Send an email with the whitepaper as attachment
	 */
	protected function sendEmail()
	{
		// get the cookie data, if none is set, we 404
		$cookieData = SpoonCookie::get('whitepaper_data');
		$cookieData = ($cookieData !== false) ? unserialize($cookieData) : array();
		if(empty($cookieData)) $this->redirect(FrontendNavigation::getURL(404));

		// set some basic email variables
		$siteTitle = FrontendModel::getModuleSetting('core', 'site_title_' . FRONTEND_LANGUAGE);
		$mailerData = FrontendModel::getModuleSetting('core', 'mailer_from');
		$replyData = FrontendModel::getModuleSetting('core', 'mailer_reply_to');
		$mailerTemplate = FRONTEND_MODULES_PATH . '/whitepapers/layout/mails/email_html.tpl';
		$detailUrl = SITE_URL . FrontendNavigation::getURLForBlock('whitepapers', 'detail') . '/' . $this->record['url'];

		// set the mail contents
		$mailParameters = array(
			'siteTitle' => $siteTitle,
			'message' => sprintf(FL::msg('WhitepaperEmailAttachment'), $this->record['title'], $detailUrl)
		);

		// send the email
		FrontendMailer::addEmail($this->record['title'], $mailerTemplate, $mailParameters,
			$cookieData['email'], $cookieData['name'], $mailerData['email'], $mailerData['name'],
			$replyData['email'], $replyData['name'], false, null, false, null, array($this->filePath)
		);

		$this->redirect($detailUrl);
	}
}
