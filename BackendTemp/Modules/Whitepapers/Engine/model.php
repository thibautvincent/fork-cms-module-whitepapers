<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * In this file we store all generic functions that we will be using in the whitepapers module
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersModel
{
	/**
	 * Query to browse all the whitepapers
	 *
	 * @var string
	 */
	const QRY_BROWSE_WHITEPAPERS =
		'SELECT w.id, w.title, w.num_downloads, UNIX_TIMESTAMP(w.created_on) AS created_on
		 FROM whitepapers AS w
		 WHERE w.language = ?';

	/**
	 * Delete a certain item
	 *
	 * @param int $id
	 */
	public static function delete($id)
	{
		$id = (int) $id;

		// initiate the database
		$db = BackendModel::getContainer()->get('database');

		// get whitepaper data
		$whitepaper = (array) $db->getRecord(
			'SELECT w.meta_id, w.filename, w.image
			 FROM whitepapers AS w
			 WHERE w.id = ?',
			array($id)
		);

		// get all the download ids for the specific whitepaper
		$downloadIds = (array) $db->getColumn(
			'SELECT d.id
			 FROM whitepapers_downloads AS d
			 WHERE d.whitepaper_id = ?',
			array($id)
		);

		// delete the data
		$db->delete('whitepapers', 'id = ?', array($id));
		$db->delete('whitepapers_downloads', 'whitepaper_id = ?', array($id));
		if(!empty($downloadIds)) $db->delete('whitepapers_downloads_values', 'download_id IN (' . implode($downloadIds) . ')');
		$db->delete('meta', 'id = ?', array((int) $whitepaper['meta_id']));

		// delete the search index
		BackendSearchModel::removeIndex('whitepapers', $id);

		// remove the whitepaper files
		$filepath = FRONTEND_FILES_PATH . '/whitepapers';
		SpoonFile::delete($filepath . '/files/' . $whitepaper['filename']);
		SpoonFile::delete($filepath . '/images/source/' . $whitepaper['image']);
		// @todo delete thumbs
	}

	/**
	 * Checks if a certain item exists
	 *
	 * @param int $id
	 * @return bool
	 */
	public static function exists($id)
	{
		return (bool) BackendModel::getContainer()->get('database')->getVar(
			'SELECT COUNT(i.id)
			 FROM whitepapers AS i
			 WHERE i.id = ?',
			array((int) $id)
		);
	}

	/**
	 * Fetches a certain item
	 *
	 * @param int $id
	 * @return array
	 */
	public static function get($id)
	{
		return (array) BackendModel::getContainer()->get('database')->getRecord(
			'SELECT i.*, m.url
			 FROM whitepapers AS i
			 INNER JOIN meta AS m ON m.id = i.meta_id
			 WHERE i.id = ?',
			array((int) $id)
		);
	}

	/**
	 * This will get all the downloads and their information
	 *
	 * @param int $whitepaperId
	 * @return array
	 */
	public static function getDownloadsInfo($whitepaperId)
	{
		$db = FrontendModel::getContainer()->get('database');
		$downloadValues = array();

		// get all the downloads for the specified whitepaper
		$downloadsData = (array) $db->getPairs(
			'SELECT d.id, UNIX_TIMESTAMP(d.downloaded_on) AS downloaded_on
			 FROM whitepapers_downloads AS d
			 WHERE d.whitepaper_id = ?
			 ORDER BY downloaded_on DESC',
			array((int) $whitepaperId)
		);

		if(!empty($downloadsData))
		{
			// get the information for each downlaod
			foreach($downloadsData as $downloadId => $download)
			{
				// get the values
				$tmpDownloadValues = $db->getPairs(
					'SELECT v.name, v.value
					 FROM whitepapers_downloads_values AS v
					 WHERE v.download_id = ?',
					array((int) $downloadId)
				);

				foreach($tmpDownloadValues as $key => $value)
				{
					$tmpDownloadValues[$key] = unserialize($value);
				}

				// add the download on date
				$tmpDownloadValues['downloaded_on'] = $downloadsData[$downloadId];

				// add the temporary data to the rest of the data
				$downloadValues[] = $tmpDownloadValues;
			}
		}

		return $downloadValues;
	}

	/**
	 * This will create a filename for a file in a specific directory.
	 *
	 * @param string $filename
	 * @param string[optional] $directory
	 * @param int[optional] $try
	 * @return string
	 */
	public static function getFilename($filename, $directory = 'files', $try = 0)
	{
		// split the filename into a name and an extension
		$extension = strrchr($filename, '.');
		$filename = substr($filename, 0, strlen($extension) * -1);
		$newFilename = $filename . '-' . md5(FrontendModel::getUTCDate());

		// build the filepath from the given directory
		$filepath = FRONTEND_FILES_PATH . '/whitepapers/' . trim($directory, '/');

		// append the try number if we've already checked the filename without it
		$newFilename = (string) ($try === 0) ? $newFilename : $newFilename . '-' . $try;
		$newFilename .= $extension;

		// check if the filename is already taken
		$existsFile = SpoonFile::exists($filepath . '/' . $newFilename);
		if($existsFile) $filename = self::getFilename($filename, $directory, $try + 1);
		return $newFilename;
	}

	/**
	 * Retrieve the unique url for an item
	 *
	 * @param string $url
	 * @param int[optional] $id
	 * @return string
	 */
	public static function getUrl($url, $id = null)
	{
		// redefine Url
		$url = SpoonFilter::urlise((string) $url);

		// get db
		$db = BackendModel::getContainer()->get('database');

		// new item
		if($id === null)
		{
			// get number of categories with this Url
			$number = (int) $db->getVar(
				'SELECT COUNT(i.id)
				 FROM whitepapers AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ?',
				array(BL::getWorkingLanguage(), $url));

			// already exists
			if($number != 0)
			{
				// add number
				$url = BackendModel::addNumber($url);
				return self::getUrl($url);
			}
		}
		// current category should be excluded
		else
		{
			// get number of items with this Url
			$number = (int) $db->getVar(
				'SELECT COUNT(i.id)
				 FROM whitepapers AS i
				 INNER JOIN meta AS m ON i.meta_id = m.id
				 WHERE i.language = ? AND m.url = ? AND i.id != ?',
				array(BL::getWorkingLanguage(), $url, $id));

			// already exists
			if($number != 0)
			{
				// add number
				$url = BackendModel::addNumber($url);
				return self::getUrl($url, $id);
			}
		}

		// return the unique Url!
		return $url;
	}

	/**
	 * Insert an item in the database
	 *
	 * @param array $data
	 * @return int
	 */
	public static function insert(array $data)
	{
		$data['created_on'] = BackendModel::getUTCDate();

		return (int) BackendModel::getContainer()->get('database')->insert('whitepapers', $data);
	}

	/**
	 * Updates an item
	 *
	 * @param	array $data		The data to update.
	 * @param	int $itemId		The item id to update.
	 */
	public static function update(array $data, $itemId)
	{
		$data['edited_on'] = BackendModel::getUTCDate();

		BackendModel::getContainer()->get('database')->update('whitepapers', $data, 'id = ?', (int) $itemId);
	}
}
