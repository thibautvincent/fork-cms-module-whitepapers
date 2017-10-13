{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblWhitepapers|ucfirst}: {$lblEdit}</h2>
</div>

{form:edit}
	<label for="title">{$lblTitle|ucfirst}</label>
	{$txtTitle} {$txtTitleError}

	<div id="pageUrl">
		<div class="oneLiner">
			{option:detailURL}<p><span><a href="{$detailURL}/{$item.url}">{$detailURL}/<span id="generatedUrl">{$item.url}</span></a></span></p>{/option:detailURL}
			{option:!detailURL}<p class="infoMessage">{$errNoModuleLinked}</p>{/option:!detailURL}
		</div>
	</div>

	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
			<li><a href="#tabDownloads">{$lblDownloads|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<table border="0" cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td id="leftColumn">

						{* Main content *}
						<div class="box">
							<div class="heading">
								<h3>{$lblDescription|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></h3>
							</div>
							<div class="optionsRTE">
								{$txtText} {$txtTextError}
							</div>
						</div>

						{* File *}
						<div class="box">
							<div class="heading">
								<h3>{$lblFile|ucfirst}</h3>
							</div>
							<div class="options">
								<p>
									<label for="file">{$lblFile|ucfirst}</label>
									{$fileFile} {$fileFileError}
								</p>
								<p>
									<a href="{$fileUrl}" title="{$item.title}">{$lblDownloadFile|ucfirst}</a>
								</p>
							</div>
						</div>

						{* Image *}
						<div class="box">
							<div class="heading">
								<h3>{$lblImage|ucfirst}</h3>
							</div>
							<div class="options">
								<p>
									<img src="{$FRONTEND_FILES_URL}/whitepapers/images/source/{$item.image}" alt="{$item.title}" style="width: 75%" />
								</p>
								<p>
									<label for="image">{$lblImage|ucfirst}</label>
									{$fileImage} {$fileImageError}
								</p>
							</div>
						</div>

					</td>

					<td id="sidebar">
						<div id="publishOptions" class="box">
							<div class="heading">
								<h3>{$lblStatus|ucfirst}</h3>
							</div>

							<div class="options">
								<ul class="inputList">
									{iteration:visible}
									<li>
										{$visible.rbtVisible}
										<label for="{$visible.id}">{$visible.label}</label>
									</li>
									{/iteration:visible}
								</ul>
							</div>

							<div class="options">
								<label for="tags">{$lblTags|ucfirst}</label>
								{$txtTags} {$txtTagsError}
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>

		<div id="tabDownloads">
			{option:!dgDownloads}
				<p>
					{$msgWhitepaperNotDownloaded}
				</p>
			{/option:!dgDownloads}

			{option:dgDownloads}
				<div class="dataGridHolder">
					{$dgDownloads}
				</div>
			{/option:dgDownloads}
		</div>

		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
		</div>
	</div>

	<div class="fullwidthOptions">
		<a href="{$var|geturl:'delete'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
			<span>{$lblDelete|ucfirst}</span>
		</a>
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblSave|ucfirst}" />
		</div>
	</div>

	<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDelete|sprintf:{$item.title}}
		</p>
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}