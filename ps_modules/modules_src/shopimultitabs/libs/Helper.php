<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

class HelperShopiMultiTab {
	public static $hookAssign = array('displayRightColumn','displayLeftColumn','displayHome','displayTop','displayFooter');
	public static function getFolderAdmin()
	{
		$folders = array('cache','classes','config','controllers','css','docs','download','img','js','localization','log','mails',
		'modules','override','themes','tools','translations','upload','webservice','.','..');
		$handle = opendir(_PS_ROOT_DIR_);
		if (!$handle)
		{
			return false;
		}
		while (false !== ($folder = readdir($handle)))
		{
			if (is_dir(_PS_ROOT_DIR_ .'/'. $folder))
			{
				if (!in_array($folder, $folders))
				{
					$folderadmin = opendir(_PS_ROOT_DIR_ .'/'. $folder);
					if (!$folderadmin) 
						return $folder;
					while (false !== ($file = readdir($folderadmin)))
					{ 
						if (is_file(_PS_ROOT_DIR_ .'/'.  $folder.'/'.$file) && ($file == 'header.inc.php'))
						{
							return $folder;
						}
					}
				}
			}
		}
		return $false;
	}
	/**
	* get modules
	* this
	*/
	public static function getModules()
	{
		//global $hookAssign;
		$notModule = array( SHOPI_MULTITAB_MODULE_NAME );
		$where = '';
		if (count($notModule) == 1)
		{
			$where = ' WHERE m.`name` <> \''.$notModule[0].'\' AND active = 1';
		}
		elseif (count($notModule) > 1)
		{
			$where = ' WHERE m.`name` NOT IN (\''.implode("','",$notModule).'\') AND active = 1';
		}
		
		$id_shop = Context::getContext()->shop->id;
		
		$modules = Db::getInstance()->ExecuteS('
		SELECT m.*
		FROM `'._DB_PREFIX_.'module` m
		JOIN `'._DB_PREFIX_.'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = '.(int)($id_shop).')
		'.$where );
		if (!$modules)
			return array();
		$return = array();
		foreach ($modules as $module)
		{
			$moduleInstance = Module::getInstanceByName($module['name']);
			$m_module = '';
			foreach (self::$hookAssign as $hook)
			{
				$retro_hook_name = Hook::getRetroHookName($hook);
				if (is_callable(array($moduleInstance, 'hook'.ucfirst($hook))) || is_callable(array($moduleInstance, 'hook'.ucfirst($retro_hook_name))))
					$m_module = $module;
			}
			if ($m_module)
				$return[] = $m_module;
		}
		return $return;
	}
	/**
	* get modules
	* 
	*/
	public static function getModuleByName( $name )
	{
		return Db::getInstance()->getRow('
		SELECT m.*
		FROM `'._DB_PREFIX_.'module` m
		JOIN `'._DB_PREFIX_.'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = '.(int)(Context::getContext()->shop->id).')
		WHERE m.`name` = \''.$name.'\'');
	}
	/**
	* get Hooks in module 
	* 
	*/
	public static function getHooksByModuleName( $name )
	{
		//global $hookAssign;
		if(!$name)
			return array();
		$moduleInstance = Module::getInstanceByName( $name );
		//echo "<pre>".print_r($moduleInstance,1); die;
		$hooks = array();
		foreach( self::$hookAssign as $hook)
		{
			$retro_hook_name = Hook::getRetroHookName($hook);
			if (is_callable(array($moduleInstance, 'hook'.ucfirst($hook))) || is_callable(array($moduleInstance, 'hook'.ucfirst($retro_hook_name))))
			{
				$hooks[] = $hook;
			}
		}
		$results = self::getHookByArrName( $hooks );
		return $results;
	}
	/**
	* get Hook by Name
	*
	*/
	public static function getHookByName($name)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT *
		FROM `'._DB_PREFIX_.'hook` 
		WHERE `name` = \''.$name.'\'');
	}
	/**
	* get Hook by name array
	*	
	*/
	public static function getHookByArrName($arrName)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_hook`, `name`
		FROM `'._DB_PREFIX_.'hook` 
		WHERE `name` IN (\''.implode("','",$arrName).'\')');
		return $result ;
	}
	
	public static function execModule( $values )
	{
		$result = '';
		if ($values )
		{
			$arrItems = explode(':',$values); 
			if (count($arrItems) == 2)
			{
				$shopimodule = self::getModuleByName( $arrItems[0] );
				$shopihook = self::getHookByName( $arrItems[1] );
				if ($shopimodule && $shopihook)
				{
					$array = array();
					$array['id_hook']   = $shopihook['id_hook'];
					$array['module'] 	= $shopimodule['name'];
					$array['id_module'] = $shopimodule['id_module'];
					if ($array['module'] && $array['id_module'] && $array['id_hook'])
						$result = self::hookExec( $shopihook['name'], array(), $shopimodule['id_module'], $array);
					/* delete module hook
					self::DeleteModuleHook( $shopimodule['id_module'], $shopihook['id_hook'] );
					*/
				}
			}
		}
		return $result;
	}
	

	public static function hookExec($hook_name, $hookArgs = array(), $id_module = NULL, $array = array())
	{
		if ((!empty($id_module) AND !Validate::isUnsignedId($id_module)) OR !Validate::isHookName($hook_name))
			die(Tools::displayError());
		
		$context = Context::getContext();
        if (!isset($hookArgs['cookie']) || !$hookArgs['cookie'])
			$hookArgs['cookie'] = $context->cookie;
		if (!isset($hookArgs['cart']) || !$hookArgs['cart'])
			$hookArgs['cart'] = $context->cart;
        
		if ($id_module && $id_module != $array['id_module'])
			return ;
		if (!($moduleInstance = Module::getInstanceByName($array['module'])) || !$moduleInstance->active)
			return ;
		$retro_hook_name = Hook::getRetroHookName($hook_name);
		
		$hook_callable = is_callable(array($moduleInstance, 'hook'.$hook_name));
		$hook_retro_callable = is_callable(array($moduleInstance, 'hook'.$retro_hook_name));
		
		$output = '';
		if (($hook_callable || $hook_retro_callable) && Module::preCall($moduleInstance->name))
		{
			if ($hook_callable)
				$output = $moduleInstance->{'hook'.$hook_name}($hookArgs);
			else if ($hook_retro_callable)
				$output = $moduleInstance->{'hook'.$retro_hook_name}($hookArgs);
		}
		return $output;
	}
}
?>