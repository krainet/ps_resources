<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

class Shopimultitab extends ObjectModel
{
	public $id;
	
	public $position;
	public $active;
	public $date_add;
	public $date_upd;
	
	public $title;
	
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = array(
		'table' => 'shopimultitab',
		'primary' => 'id_shopimultitab',
		'multilang' => true,
		'fields' => array(
			'active' =>			array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
			'position' =>		array('type' => self::TYPE_INT, 'validate' => 'isunsignedInt'),
			
			'title' =>			array('type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 255),
		)
	);
	
	public function __construct($id = null, $id_lang = null, $id_shop = null, Context $context = null)
	{
		parent::__construct($id, $id_lang, $id_shop);
	}

	public function add($autodate = true, $null_values = false)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$this->position = $this->getLastPosition();
		
		$res = parent::add($autodate, $null_values);
		
		$res &= Db::getInstance()->execute('
			INSERT INTO `'._DB_PREFIX_.'shopimultitab_shop` (`id_shop`, `id_shopimultitab`)
			VALUES('.(int)$id_shop.', '.(int)$this->id.')'
		);
		return $res;
	}

	public function delete()
	{
		$res = true;
		$res &= $this->reOrderPositions();

		$res &= Db::getInstance()->execute('
			DELETE FROM `'._DB_PREFIX_.'shopimultitab_shop`
			WHERE `id_shopimultitab` = '.(int)$this->id
		);
		$contents = ShopimultitabContent::getContents($this->id);
		if ($contents)
			foreach ($contents as $content)
			{
				$obj = new ShopimultitabContent((int)$content['id_shopimultitab_content']);
				$res &= $obj->delete();
			}
		$res &= parent::delete();
		return $res;
	}
	
	public static function getLastPosition()
	{
		return (Db::getInstance()->getValue('SELECT MAX(l.position)+1 FROM `'._DB_PREFIX_.'shopimultitab` l'));
	}
	
	public function reOrderPositions()
	{
		$id_shopimultitab = $this->id;
		$context = Context::getContext();
		$id_shop = $context->shop->id;

		$max = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT MAX(hss.`position`) as position
			FROM `'._DB_PREFIX_.'shopimultitab` hss, `'._DB_PREFIX_.'shopimultitab_shop` hs
			WHERE hss.`id_shopimultitab` = hs.`id_shopimultitab` AND hs.`id_shop` = '.(int)$id_shop
		);

		if ((int)$max == (int)$id_shopimultitab)
			return true;

		$rows = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hss.`position` as position, hss.`id_shopimultitab` as id_shopimultitab
			FROM `'._DB_PREFIX_.'shopimultitab` hss
			LEFT JOIN `'._DB_PREFIX_.'shopimultitab_shop` hs ON (hss.`id_shopimultitab` = hs.`id_shopimultitab`)
			WHERE hs.`id_shop` = '.(int)$id_shop.' AND hss.`position` > '.(int)$this->position
		);

		foreach ($rows as $row)
		{
			$current_tab = new Shopimultitab($row['id_shopimultitab']);
			$current_tab->position;
			$current_tab->update();
			unset($current_tab);
		}

		return true;
	}
	
	public static function getTabs($active = null)
	{
		$context = Context::getContext();
		$id_shop = $context->shop->id;
		$id_lang = $context->language->id;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
			SELECT hs.`id_shopimultitab` as id_shopimultitab,
					   hss.`position`, hss.`active`,
					   hssl.`title`
			FROM '._DB_PREFIX_.'shopimultitab_shop hs
			LEFT JOIN '._DB_PREFIX_.'shopimultitab hss ON (hs.id_shopimultitab = hss.id_shopimultitab)
			LEFT JOIN '._DB_PREFIX_.'shopimultitab_lang hssl ON (hss.id_shopimultitab = hssl.id_shopimultitab)
			WHERE (id_shop = '.(int)$id_shop.')
			AND hssl.id_lang = '.(int)$id_lang.
			($active ? ' AND hss.`active` = 1' : ' ').'
			ORDER BY hss.position'
			);
	}

}
