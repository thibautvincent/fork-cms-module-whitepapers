{option:whitepaper}
	<div id="whitepapersIndex">
		<article class="mod">
			<div class="inner">
				<header class="hd">
					<h1 itemprop="name">{$whitepaper.title}</h1>
					<ul>
						<li>
							<time itemprop="datePublished" datetime="{$whitepaper.created_on|date:'Y-m-d\TH:i:s'}">{$whitepaper.created_on|date:{$dateFormatLong}:{$LANGUAGE}}</time>
						</li>
					</ul>
				</header>

				{* Share widget *}
				<p>
					<a href="{$SITE_URL}{$whitepaper.full_url}" class="share">{$lblShare|ucfirst}</a>
				</p>

				{* Whitepaper content *}
				<div class="bd content" itemprop="articleBody">
					{* @todo thumbs *}
					{option:whitepaper.image}<img src="{$FRONTEND_FILES_URL}/whitepapers/images/source/{$whitepaper.image}" width="150" alt="{$whitepaper.title}" itemprop="image" />{/option:whitepaper.image}
					{$whitepaper.text}
				</div>

				{* Share widget *}
				<p>
					<a href="{$SITE_URL}{$whitepaper.full_url}" class="share">{$lblShare|ucfirst}</a>
				</p>

				{* Whitepaper download form *}
				{option:showDataForm}
					<div class="downloadForm">
						<h3>{$lblDownloadWhitepaper|ucfirst}</h3>
						<p class="whitepaperTitle">
							{$whitepaper.title}
						</p>

						{option:downloadMessage}
							<div class="{$messageClass}">
								<p>
									{$downloadMessage}
								</p>
							</div>
						{/option:downloadMessage}

						{option:submitMessage}
							<div class="{$messageClass}">
								<p>
									{$submitMessage}
								</p>
							</div>
						{/option:submitMessage}

						{option:!downloadMessage}
							<p>
								{$msgDownloadWhitepaperInfo}
							</p>
							{form:download}
								<p>
									<label for="name">{$lblName|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
									{$txtName} {$txtNameError}
								</p>
								<p>
									<label for="email">{$lblEmail|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
									{$txtEmail} {$txtEmailError}
								</p>
								<p>
									<label for="phone">{$lblPhonenumber|ucfirst}</label>
									{$txtPhone} {$txtPhoneError}
								</p>
								{option:newsletter}
								<p>
									<label for="newsletter">{$chkNewsletter} {$msgKeepInformed}</label>
								</p>
								{/option:newsletter}
								<p>
									<input class="inputSubmit" type="submit" name="comment" value="{$lblDownload|ucfirst}" />
								</p>
							{/form:download}
						{/option:!downloadMessage}
					</div>
				{/option:showDataForm}

				{* Download the whitepaper before submitting the form *}
				{option:!showDataForm}
					<p>
						<a href="{$var|geturlforblock:'whitepapers':'detail'}/{$whitepaper.url}?sent=true&download=true" title="{$lblDownload}">{$lblDownload|ucfirst}</a>
					</p>
				{/option:!showDataForm}
			</div>
		</article>
	</div>
{/option:whitepaper}