<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the Download action, it will force you to download a certain whitepaper
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersDownload extends BackendBaseAction
{
	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$whitepaperId = $this->getParameter('id', 'int', null);

		// download the whitepaper if it exists
		if(BackendWhitepapersModel::exists($whitepaperId))
		{
			$whitepaper = BackendWhitepapersModel::get($whitepaperId);
			$filename = $whitepaper['filename'];
			$filepath = FRONTEND_FILES_PATH . '/whitepapers/files/' . $filename;
			$fileExtension = strrchr($filename, '.');

			// add extra extensions and content type if needed.
			switch($fileExtension)
			{
				case '.pdf':
				default:
					$contentType = 'application/pdf';
					break;
			}

			// the file does not exist, redirect
			if(!SpoonFile::exists($filepath)) $this->redirect(BackendModel::createURLForAction('edit') . '&amp;id=' . $whitepaperId . '&amp;error=non-existing');

			// set the headers and download the file
			SpoonHTTP::setHeaders(array(
				'Content-Disposition: attachment; filename="' . $whitepaper['filename'] . '"',
				'Content-Type: ' . $contentType,
				'Content-Length: ' . filesize($filepath)
			));
			echo SpoonFile::getContent($filepath);
			exit;
		}
		// the item does not exist
		else $this->redirect(BackendModel::createURLForAction() . '&amp;error=non-existing');
	}
}
