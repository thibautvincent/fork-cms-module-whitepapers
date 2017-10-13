<?php

/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * This is the add-action, it will display a form to create a new item
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
class BackendWhitepapersAdd extends BackendBaseActionAdd
{
	/**
	 * Execute the actions
	 */
	public function execute()
	{
		parent::execute();

		$this->loadForm();
		$this->validateForm();

		$this->parse();
		$this->display();
	}

	/**
	 * Load the form
	 */
	protected function loadForm()
	{
		$rbtVisibleValues[] = array('label' => BL::lbl('Hidden'), 'value' => 'N');
		$rbtVisibleValues[] = array('label' => BL::lbl('Published'), 'value' => 'Y');

		$this->frm = new BackendForm('add');
		$this->frm->addText('title', null, null, 'inputText title', 'inputTextError title');
		$this->frm->addEditor('text');
		$this->frm->addFile('file')->setAttribute('extension', 'pdf');
		$this->frm->addImage('image');
		$this->frm->addRadiobutton('visible', $rbtVisibleValues, 'Y');
		$this->frm->addText('tags', null, null, 'inputText tagBox', 'inputTextError tagBox');

		$this->meta = new BackendMeta($this->frm, null, 'title', true);
	}

	/**
	 * Parse the page
	 */
	protected function parse()
	{
		parent::parse();

		// assign the url for the detail page
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

			if($fields['image']->isFilled(BL::err('FieldIsRequired')))
			{
				// do minimum size checks
			}

			if($fields['file']->isFilled(BL::err('FieldIsRequired')))
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
				$item['filename'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . '.' . $fields['file']->getExtension());
				$item['image'] = BackendWhitepapersModel::getFilename($this->meta->getURL() . '.' . $fields['image']->getExtension(), 'images/source');
				$item['visible'] = $fields['visible']->getValue();
				$item['id'] = BackendWhitepapersModel::insert($item);

				// upload the file
				$fields['file']->moveFile($filepath . '/files/' . $item['filename']);

				// upload the image
				// @todo create thumbnails
				$fields['image']->moveFile($filepath . '/images/source/' . $item['image']);

				// save the tags
				BackendTagsModel::saveTags($item['id'], $fields['tags']->getValue(), $this->URL->getModule());

				BackendSearchModel::saveIndex(
					$this->getModule(),
					$item['id'],
					array('title' => $item['title'], 'text' => $item['title'])
				);

				BackendModel::triggerEvent($this->getModule(), 'after_add', $item);
				$this->redirect(BackendModel::createURLForAction('index') . '&report=added&highlight=row-' . $item['id']);
			}
		}
	}
}
