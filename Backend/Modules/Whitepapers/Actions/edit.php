<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the edit-action, it will display a form with the item data to edit
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersEdit extends BackendBaseActionEdit
{
	/**
	 * The downloads dataGrid
	 *
	 * @var BackendDataGridArray
	 */
	private $downloadsDataGrid;

	/**
	 * Execute the action
	 */
	public function execute()
	{
		parent::execute();

		$this->loadData();
		$this->loadDownloadsDataGrid();
		$this->loadForm();

		$this->validateForm();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the item data
	 */
	protected function loadData()
	{
		$this->id = $this->getParameter('id', 'int', null);
		if($this->id == null || !BackendWhitepapersModel::exists($this->id))
		{
			$this->redirect(BackendModel::createURLForAction('index') . '&error=non-existing');
		}

		$this->record = BackendWhitepapersModel::get($this->id);
	}

	/**
	 * This will build the dataGrid for the downloads
	 */
	protected function loadDownloadsDataGrid()
	{
		$downloadsData = BackendWhitepapersModel::getDownloadsInfo($this->id);

		$this->downloadsDataGrid = new BackendDataGridArray($downloadsData);
		$this->downloadsDataGrid->setHeaderLabels(array('phone' => ucfirst(BL::lbl('Phonenumber'))));
		$this->downloadsDataGrid->setSortingColumns(array('email', 'name', 'phone', 'downloaded_on'), 'downloaded_on');
		$this->downloadsDataGrid->setSortParameter();
		$this->downloadsDataGrid->setColumnFunction(array('BackendDatagridFunctions', 'getLongDate'), array('[downloaded_on]'), array('downloaded_on'));
		$this->downloadsDataGrid->setURL('&id=' . $this->id . '#tabDownloads', true);
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		// set hidden values
		$rbtVisibleValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'N');
		$rbtVisibleValues[] = array('label' => BL::lbl('Published'), 'value' => 'Y');

		// create form
		$this->frm = new BackendForm('edit');
		$this->frm->addText('title', $this->record['title'], null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('text', $this->record['text']);
		$this->frm->addFile('file')->setAttribute('extension', 'pdf');
		$this->frm->addImage('image');
		$this->frm->addRadiobutton('visible', $rbtVisibleValues, $this->record['visible']);
		$this->frm->addText('tags', BackendTagsModel::getTags($this->URL->getModule(), $this->record['id']), null, 'inputText tagBox', 'inputTextError tagBox');

		// meta
		$this->meta = new BackendMeta($this->frm, $this->record['meta_id'], 'title', true);
		$this->meta->setUrlCallback('BackendWhitepapersModel', 'getUrl', array($this->record['id']));
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();
		$this->tpl->assign('item', $this->record);
		$this->tpl->assign('fileUrl', BackendModel::createURLForAction('download') . '&amp;id=' . $this->id);
		$this->tpl->assign('dgDownloads', ($this->downloadsDataGrid->getNumResults() > 0) ? $this->downloadsDataGrid->getContent() : false);

		// get url
		$url = BackendModel::getURLForBlock($this->URL->getModule(), 'detail');
		$url404 = BackendModel::getURL(404);
		if($url404 != $url) $this->tpl->assign('detailURL', SITE_URL . $url);
	}

	/**
	 * Validate the form
	 */
	protected function validateForm()
	{
		if($this->frm->isSubmitted())
		{
			$this->frm->cleanupFields();

			// validation
			$fields = $this->frm->getFields();
			$fields['title']->isFilled(BL::err('FieldIsRequired'));
			$fields['text']->isFilled(BL::err('FieldIsRequired'));

			if($fields['image']->isFilled())
			{
				// do minimum size checks
			}

			if($fields['file']->isFilled())
			{
				$fields['file']->isAllowedExtension(array('pdf'), sprintf(BL::err('ExtensionNotAllowed'), 'pdf'));
			}

			$this->meta->validate();

			if($this->frm->isCorrect())
			{
				// the basic file directory
				$filepath = FRONTEND_FILES_PATH . '/whitepapers';

				$item['meta_id'] = $this->meta->save();
				$item['language'] = BL::getWorkingLanguage();
				$item['title'] = $fields['title']->getValue();
				$item['text'] = $fields['text']->getValue();
				$item['visible'] = $fields['visible']->getValue();

				// new file given, upload this one and remove the old one
				if($fields['file']->isFilled())
				{
					$item['filename'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . '.' . $fields['file']->getExtension());
					SpoonFile::delete($filepath . '/files/' . $this->record['filename']);

					// upload the new file
					$fields['file']->moveFile($filepath . '/files/' . $item['filename']);
				}
				// no new file given, rename the old file to the new name if needed
				else
				{
					// get the extension from the old file
					$fileExtension = strrchr($this->record['filename'], '.');
					$item['filename'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . $fileExtension);

					// rename the old file
					SpoonFile::move($filepath . '/files/' . $this->record['filename'], $filepath . '/files/' . $item['filename']);
				}

				// new image given, upload this one and remove the old ones
				if($fields['image']->isFilled())
				{
					$item['image'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . '.' . $fields['image']->getExtension(), 'images/source');
					SpoonFile::delete($filepath . '/images/source/' . $this->record['image']);

					// @todo delete and create thumbnails
					$fields['image']->moveFile($filepath . '/images/source/' . $item['image']);
				}
				// no new image givem, rename the old image
				else
				{
					// get the extension from the old file
					$imageExtension = strrchr($this->record['image'], '.');
					$item['image'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . $imageExtension, 'images/source');

					// @todo rename thumbs
					SpoonFile::move($filepath . '/images/source/' . $this->record['image'], $filepath . '/images/source/' . $item['image']);
				}

				// save the item
				BackendWhitepapersModel::update($item, $this->id);
				$item['id'] = $this->id;

				// save the tags
				BackendTagsModel::saveTags($item['id'], $fields['tags']->getValue(), $this->URL->getModule());

				BackendSearchModel::saveIndex(
					$this->getModule(),
					$item['id'],
					array('title' => $item['title'], 'text' => $item['title'])
				);

				BackendModel::triggerEvent($this->getModule(), 'after_edit', $item);
				$this->redirect(BackendModel::createURLForAction('index') . '&report=edited&highlight=row-' . $item['id']);
			}
		}
	}
}
