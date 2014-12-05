
CREATE TABLE IF NOT EXISTS `#__revslider_sliders` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `alias` varchar(255) NOT NULL default '',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `#__revslider_slides` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `slider_id` int(10) unsigned NOT NULL default '0',
  `slide_order` int(11) NOT NULL,
  `params` text NOT NULL,
  `layers` text NOT NULL,   
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__revslider_settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `general` text NOT NULL,   
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__revslider_css` (
  `id` int(10) unsigned NOT NULL auto_increment,  
  `handle` text NOT NULL,
  `settings` text,
  `hover` text,
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__revslider_layer_animations` (
  `id` int(10) unsigned NOT NULL auto_increment,  
  `handle` text NOT NULL,
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)  
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



