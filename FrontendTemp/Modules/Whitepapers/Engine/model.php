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
class FrontendWhitepapersModel
{
	/**
	 * Add a download to a specific whitepaper
	 *
	 * @param int $whitepaperId
	 */
	public static function addDownload($whitepaperId)
	{
		FrontendModel::getContainer()->get('database')->execute(
			'UPDATE whitepapers
			 SET num_downloads = (num_downloads + 1)
			 WHERE id = ?',
			array((int) $whitepaperId)
		);
	}

	/**
	 * Builds the meta
	 *
	 * @param array $data The data to convert the meta from.
	 * @return array
	 */
	public static function buildMetaData(array $data)
	{
		// return if no data is given
		if(empty($data)) return array();

		// the meta
		$meta = array();

		// loop the data
		foreach($data as $key => $column)
		{
			// if there is meta_ set in the column name
			if(strpos($key, 'meta_') !== false)
			{
				$metaKey = substr($key, 5);
				$meta[$metaKey] = $column;
				unset($data[$key]);
			}
		}

		// add the meta
		$data['meta'] = $meta;

		// return
		return $data;
	}

	/**
	 * Count all the visible items for the frontend language
	 *
	 * @return int
	 */
	public static function getAllCount()
	{
		return (int) FrontendModel::getContainer()->get('database')->getNumRows(
			'SELECT w.id
			 FROM whitepapers AS w
			 WHERE w.visible = ? AND w.language = ?',
			array('Y', FRONTEND_LANGUAGE)
		);
	}

	/**
	 * Get allt he items for a specific range
	 *
	 * @param int[optional] $limit
	 * @param int[optional] $offset
	 * @return array
	 */
	public static function getAllForRange($limit = 30, $offset = 0)
	{
		$whitepapersData = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT w.title, w.text, w.image, UNIX_TIMESTAMP(w.created_on) AS created_on, m.url
			 FROM whitepapers AS w
			 INNER JOIN meta AS m ON m.id = w.meta_id
			 WHERE w.visible = ? AND w.language = ?
			 ORDER BY w.created_on DESC
			 LIMIT ?, ?',
			array('Y', FRONTEND_LANGUAGE, (int) $offset, (int) $limit)
		);

		return self::processWhitepaperData($whitepapersData);
	}

	/**
	 * This will fetch the data for a given id
	 *
	 * @param int $id
	 * @return array
	 */
	public static function getDataForId($id)
	{
		$whitepaperData = (array) FrontendModel::getContainer()->get('database')->getRecord(
			'SELECT
			 	w.id, w.title, w.text, w.filename, w.image, m.url,
			 	UNIX_TIMESTAMP(w.created_on) AS created_on, m.url, m.title AS meta_title,
			 	m.description AS meta_description, m.keywords AS meta_keywords,
			 	m.title_overwrite AS meta_title_overwrite,
			 	m.description_overwrite AS meta_description_overwrite,
			 	m.keywords_overwrite AS meta_keywords_overwrite
			 FROM whitepapers AS w
			 INNER JOIN meta AS m ON m.id = w.meta_id
			 WHERE w.id = ? AND w.visible = ? AND w.language = ?',
			array((int) $id, 'Y', FRONTEND_LANGUAGE)
		);

		// build the full url for the specific item
		$whitepaperDetailUrl = FrontendNavigation::getURLForBlock('whitepapers', 'detail');
		$whitepaperData['full_url'] = $whitepaperDetailUrl . '/' . $whitepaperData['url'];

		// get the meta data
		$whitepaperData = self::buildMetaData($whitepaperData);

		return $whitepaperData;
	}

	/**
	 * This function will fetch the id for a whitepaper from its url
	 *
	 * @param string $url
	 * @return int
	 */
	public static function getIdForUrl($url)
	{
		return (int) FrontendModel::getContainer()->get('database')->getVar(
			'SELECT w.id
			 FROM whitepapers AS w
			 INNER JOIN meta AS m ON m.id = w.meta_id
			 WHERE m.url = ? AND w.visible = ? AND w.language = ?',
			array((string) $url, 'Y', FRONTEND_LANGUAGE)
		);
	}

	/**
	 * Fetch multiple items from their ids
	 *
	 * @param array $ids
	 * @param int[optional] $limit
	 * @param int[optional] $offset
	 * @return array
	 */
	public static function getMultipleItemsForRange(array $ids, $limit = 30, $offset = 0)
	{
		// if there are no ids, return an empty array
		if(empty($ids)) return array();

		$whitepapersData = (array) FrontendModel::getContainer()->get('database')->getRecords(
			'SELECT w.title, w.text, w.image, UNIX_TIMESTAMP(w.created_on) AS created_on, m.url
			 FROM whitepapers AS w
			 INNER JOIN meta AS m ON m.id = w.meta_id
			 WHERE w.visible = ? AND w.language = ? AND w.id IN (' . implode(',', $ids) . ')
			 ORDER BY w.created_on DESC
			 LIMIT ?, ?',
			array('Y', FRONTEND_LANGUAGE, (int) $offset, (int) $limit)
		);

		return self::processWhitepaperData($whitepapersData);
	}

	/**
	 * This will process the whitepaper data and add or strip data if needed.
	 *
	 * @param array $whitepapersData
	 * @return array
	 */
	public static function processWhitepaperData(array $whitepapersData)
	{
		// build the whitepapers full url
		$detailUrl = FrontendNavigation::getURLForBlock('whitepapers', 'detail');
		foreach($whitepapersData as $key => $value)
		{
			$whitepapersData[$key]['full_url'] = $detailUrl . '/' . $value['url'];
		}

		return $whitepapersData;
	}

	/**
	 * This function will save all the values from the download form
	 *
	 * @param int $whitepaperId
	 * @param array $downloadData This is an array with form fields to store in the database
	 * @return array This will return the generated data from the form fields
	 */
	public static function saveDownloadData($whitepaperId, array $downloadData)
	{
		unset(
			$downloadData['form'], $downloadData['_utf8'], $downloadData['form_token'],
			$downloadData['newsletter']
		);
		$db = FrontendModel::getContainer()->get('database');

		// insert the download
		$downloadInfo = array(
			'whitepaper_id' => (int) $whitepaperId,
			'downloaded_on' => FrontendModel::getUTCDate(),
			'data' => serialize($_SERVER)
		);
		$downloadId = $db->insert('whitepapers_downloads', $downloadInfo);

		// go trough the values and assign the values
		$downloadValues = array();
		foreach($downloadData as $fieldName => $field)
		{
			$downloadValues[] = array(
				'download_id' => $downloadId,
				'name' => (string) $fieldName,
				'value' => serialize($field->getValue())
			);
		}

		// insert the download values
		$db->insert('whitepapers_downloads_values', $downloadValues);

		return $downloadValues;
	}
}
