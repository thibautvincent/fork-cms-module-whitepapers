CREATE TABLE IF NOT EXISTS `whitepapers` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `meta_id` int(11) unsigned NOT NULL,
  `language` varchar(5) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `text` text collate utf8_unicode_ci NOT NULL,
  `filename` varchar(255) collate utf8_unicode_ci NOT NULL,
  `image` varchar(255) collate utf8_unicode_ci NOT NULL,
  `visible` enum('N','Y') collate utf8_unicode_ci NOT NULL default 'Y',
  `num_downloads` int(11) unsigned NOT NULL default '0',
  `created_on` datetime NOT NULL,
  `edited_on` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `fk_overview_search` (`language`,`visible`),
  KEY `fk_detail_search` (`meta_id`,`language`,`visible`),
  KEY `num_downloads_search` (`num_downloads`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `whitepapers_downloads` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `whitepaper_id` int(11) unsigned NOT NULL,
  `downloaded_on` datetime NOT NULL,
  `data` text collate utf8_unicode_ci COMMENT 'Serialized data from the user.',
  PRIMARY KEY  (`id`),
  KEY `whitepaper_id` (`whitepaper_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `whitepapers_downloads_values` (
  `download_id` int(11) unsigned NOT NULL,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL,
  `value` text collate utf8_unicode_ci NOT NULL COMMENT 'Serialized date for the name field',
  UNIQUE KEY `fk_unique_key` (`download_id`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;