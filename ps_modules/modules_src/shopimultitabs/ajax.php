<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

include_once('../../config/config.inc.php');
include_once('../../init.php');
include_once( dirname(__FILE__).'/shopimultitabs.php' );
$obj = new shopimultitabs();

if (Tools::getValue('secure_key') == $obj->secure_key && Tools::getValue('task') == 'tab')
{
	$tabs = Shopimultitab::getTabs();
	$str = '';
	if ($tabs)
	{
		foreach ($tabs as $tab)
			$str .= '<a class="list-group-item shopi-tab-row" href="javascript:void(0)" id="link-shopi-tab-'.$tab['id_shopimultitab'].'" rel="shopi-tab-'.$tab['id_shopimultitab'].'">'.$tab['title'].'</a>';
	}
	die($str);
}
if (Tools::getValue('secure_key') == $obj->secure_key && Tools::getValue('task') == 'content')
{
	$id_product = Tools::getValue('id_product');
	$str = $obj->contentTabs($id_product);
	die('<script type="text/javascript" src="'._MODULE_DIR_.$obj->name.'/views/js/general.js"></script>'.$str);
}
if (Tools::getValue('secure_key') == $obj->secure_key && Tools::getValue('task') == 'updatePosition')
{
	
	$tabs = Tools::getValue('tabs');
	foreach ($tabs as $position => $id_shopimultitab)
	{
		$res = Db::getInstance()->execute('
			UPDATE `'._DB_PREFIX_.'shopimultitab` SET `position` = '.(int)$position.'
			WHERE `id_shopimultitab` = '.(int)$id_shopimultitab
		);
	}
}
if (Tools::getValue('secure_key') == $obj->secure_key && Tools::getValue('task') == 'gethook')
{
	$module_name = Tools::getValue('module_name');
	$str = $obj->strHook($module_name);
	die($str);
}
