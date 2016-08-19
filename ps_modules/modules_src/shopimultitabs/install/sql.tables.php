<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

$query = "
CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab` (
  `id_shopimultitab` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `date_add` datetime NOT NULL,
  `date_upd` datetime NOT NULL,
  PRIMARY KEY (`id_shopimultitab`)
) ENGINE=_MYSQL_ENGINE_  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab_lang` (
  `id_shopimultitab` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) DEFAULT NULL,
  `id_lang` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_shopimultitab`,`id_lang`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab_shop` (
  `id_shopimultitab` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_shopimultitab`,`id_shop`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab_content` (
  `id_shopimultitab_content` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `content` text NOT NULL,
  `id_shopimultitab` int(11) NOT NULL,
  `categories` varchar(255) DEFAULT NULL,
  `global` tinyint(1) NOT NULL,
  PRIMARY KEY (`id_shopimultitab_content`)
) ENGINE=_MYSQL_ENGINE_  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab_content_lang` (
  `id_shopimultitab_content` int(11) NOT NULL,
  `id_lang` int(11) NOT NULL,
  `content_text` text NOT NULL,
  PRIMARY KEY (`id_shopimultitab_content`,`id_lang`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_shopimultitab_content_shop` (
  `id_shopimultitab_content` int(11) NOT NULL,
  `id_shop` int(11) NOT NULL,
  PRIMARY KEY (`id_shopimultitab_content`,`id_shop`)
) ENGINE=_MYSQL_ENGINE_ DEFAULT CHARSET=utf8;
";
