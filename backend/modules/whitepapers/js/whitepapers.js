/*
 * This file is part of Fork CMS.
 *
 * For the full copyright and license information, please view the license
 * file that was distributed with this source code.
 */

/**
 * Interaction for the whitepapers module
 *
 * @author Jelmer Snoeck <jelmer.snoeck@netlash.com>
 */
jsBackend.whitepapers =
{
	// constructor
	init: function()
	{
		// do meta
		if($('#title').length > 0) $('#title').doMeta();
		
		// show the send in mail option or not
		var showSendMail = (typeof $('#submitAfterDownload:checked').val() == 'undefined');
		if(showSendMail) $('.sendInMail').show();
		
		// bind the click event
		$('#submitAfterDownload').bind('click', function()
		{
			showSendMail = !showSendMail;
			if(showSendMail) $('.sendInMail').show();
			else 
			{
				$('.sendInMail').hide();
				$('#sendInMail').attr('checked', false);
			}
		});
	}
}


$(jsBackend.whitepapers.init);