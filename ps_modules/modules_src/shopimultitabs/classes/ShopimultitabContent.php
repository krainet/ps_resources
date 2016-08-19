<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

class ShopimultitabContent extends ObjectModel
{
	public $id;
	
	public $id_product;
	public $content;
	public $id_shopimultitab;
	public $categories;
	public $global;
	
	public $content_text;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'shopimultitab_content',
		'primary' => 'id_shopimultitab_content',
		'multilang' => true,
		'fields' => array(
			'id_product' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'content' =>			array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'size' => 255),
			'id_shopimultitab' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt', 'required' => true),
			'categories' =>		array('type' => self::TYPE_STRING, 'validate' => 'isCleanHtml'),
			'global' =>		array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
			
			'content_text' =>	array('type' => self::TYPE_HTML, 'lang' => true),
		)
	);
	
	public	function __construct($id = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
		parent::__construct($id, $id_lang, $id_shop);
	}

	public function add($autodate = true, $null_values = false)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$res = parent::add($autodate, $null_values);
		
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'shopimultitab_content_shop` (`id_shop`, `id_shopimultitab_content`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}

	public function delete()
	{
		$res = true;
		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'shopimultitab_content_shop`
			WHERE `id_shopimultitab_content` = '.(int)$this->id
		);
		$res &= parent::delete();
		return $res;
	}
	
	public static function checkExist($id_shopimultitab, $id_product)
	{
		$return = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT *
			FROM '._DB_PREFIX_.'shopimultitab_content
			WHERE id_shopimultitab = '.(int)$id_shopimultitab.' AND id_product = '.(int)($id_product) );
		return ($return ? true : false);
	}
	
	public static function getContents($id_shopimultitab = null, $id_product = null, $global = null)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$id_lang = $context->language->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_shopimultitab_content` as id_shopimultitab_content, hss.`content`, hss.`id_product`, hss.`id_shopimultitab`,
					   hssl.`content_text`, hss.categories, hss.global
			FROM '._DB_PREFIX_.'shopimultitab_content_shop hs
			LEFT JOIN '._DB_PREFIX_.'shopimultitab_content hss ON (hs.id_shopimultitab_content = hss.id_shopimultitab_content)
			LEFT JOIN '._DB_PREFIX_.'shopimultitab_content_lang hssl ON (hss.id_shopimultitab_content = hssl.id_shopimultitab_content)
			WHERE (id_shop = '.(int)$id_shop.')
			AND hssl.id_lang = '.(int)$id_lang.($id_shopimultitab ? ' AND hss.`id_shopimultitab` = '.(int)$id_shopimultitab : '').($id_product ? ' AND hss.`id_product` = '.(int)$id_product : '')
			.($global ? ' AND hss.global = 1' : '')
			);
	}
	
	public static function getContent($id_shopimultitab, $id_product)
	{
		if (!$id_shopimultitab || !$id_product)
			return array();
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$id_lang = $context->language->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT hs.`id_shopimultitab_content` as id_shopimultitab_content, hss.`content`, hss.`id_product`, hss.`id_shopimultitab`,
					   hssl.`content_text`
			FROM '._DB_PREFIX_.'shopimultitab_content_shop hs
			LEFT JOIN '._DB_PREFIX_.'shopimultitab_content hss ON (hs.id_shopimultitab_content = hss.id_shopimultitab_content)
			LEFT JOIN '._DB_PREFIX_.'shopimultitab_content_lang hssl ON (hss.id_shopimultitab_content = hssl.id_shopimultitab_content)
			WHERE (id_shop = '.(int)$id_shop.')
			AND hssl.id_lang = '.(int)$id_lang.' AND hss.`id_shopimultitab` = '.(int)$id_shopimultitab.($id_product ? ' AND hss.`id_product` = '.(int)$id_product : '')
			);
	}

}
