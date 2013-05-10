{*
	Available data:
	- {$items} The items
*}

{option:!items}
	<div id="whitepapersIndex">
		<section class="mod">
			<div class="inner">
				<div class="bd content">
					<p>{$msgNoWhitepapers}</p>
				</div>
			</div>
		</section>
	</div>
{/option:!items}

{option:items}
	<div id="whitepapersIndex">
		{iteration:items}
			<article class="mod">
				<div class="inner">
					<header class="hd">
						<h3><a href="{$items.full_url}" title="{$items.title}">{$items.title}</a></h3>
						<ul>
							<li>{$items.created_on|date:{$dateFormatLong}:{$LANGUAGE}|ucfirst}</li>
						</ul>
					</header>
					<div class="bd content">
						{* @todo thumbs *}
						{option:items.image}<a href="{$items.full_url}" title="{$items.title}"><img src="{$FRONTEND_FILES_URL}/whitepapers/images/source/{$items.image}" width="150" alt="{$items.title}" /></a>{/option:items.image}
						{$items.text|truncate:350}
						<p>
							<a href="{$items.full_url}" title="{$items.title}">{$lblReadMoreAndDownload|ucfirst}</a>
						</p>
					</div>
				</div>
			</article>
		{/iteration:items}
	</div>
	{include:core/layout/templates/pagination.tpl}
{/option:items}