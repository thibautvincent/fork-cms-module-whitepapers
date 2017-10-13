{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblModuleSettings|ucfirst}: {$lblWhitepapers}</h2>
</div>

{form:settings}
	<div class="box">
		<div class="heading">
			<h3>{$lblPagination|ucfirst}</h3>
		</div>
		<div class="options">
			<label for="overviewNumberOfItems">{$lblItemsPerPage|ucfirst}</label>
			{$ddmOverviewNumberOfItems} {$ddmOverviewNumberOfItemsError}
		</div>
		<div class="options">
			<label for="recentWhitepapersNumberOfItems">{$msgNumItemsInRecentWhitepapers|ucfirst}</label>
			{$ddmRecentWhitepapersNumberOfItems} {$ddmRecentWhitepapersNumberOfItemsError}
		</div>
		<div class="options">
			<label for="relatedWhitepapersNumberOfItems">{$msgNumItemsInRelatedWhitepapers|ucfirst}</label>
			{$ddmRelatedWhitepapersNumberOfItems} {$ddmRelatedWhitepapersNumberOfItemsError}
		</div>
	</div>

	<div class="box">
		<div class="heading">
			<h3>{$lblDownloads|ucfirst}</h3>
		</div>
		<div class="options">
			<ul class="inputList">
				<li><label for="submitAfterDownload">{$chkSubmitAfterDownload} {$msgSubmitAfterDownload}</label></li>
				<li class="sendInMail" style="display: none;"><label for="sendInMail">{$chkSendInMail} {$msgSendInMail}</label></li>
			</ul>
		</div>
	</div>

	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="save" class="inputButton button mainButton" type="submit" name="save" value="{$lblSave|ucfirst}" />
		</div>
	</div>
{/form:settings}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}