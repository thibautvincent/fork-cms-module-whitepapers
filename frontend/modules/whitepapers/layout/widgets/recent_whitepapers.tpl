{*
	variables that are available:
	- {$widgetRecentWhitepapers}: contains all the data for this widget
*}
{option:widgetRecentWhitepapers}
	<div class="recentWhitepapers">
		<h3>{$lblRecentWhitepapers|ucfirst}</h3>
		<ul>
			{iteration:widgetRecentWhitepapers}
			<li>
				{$widgetRecentWhitepapers.created_on|date:{$dateFormatLong}:{$LANGUAGE}} ({$widgetRecentWhitepapers.created_on|timeago})<br />
				<a href="{$widgetRecentWhitepapers.full_url}" title="{$widgetRecentWhitepapers.title}">{$widgetRecentWhitepapers.title}</a>
			</li>
			{/iteration:widgetRecentWhitepapers}
		</ul>
		<p>
			<a href="{$var|geturlforblock:'whitepapers'}" title="{$lblAllWhitepapers|ucfirst}">{$lblAllWhitepapers|ucfirst}</a>
		</p>
	</div>
{/option:widgetRecentWhitepapers}