<?php
/**
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
 */

if (!defined('_PS_VERSION_'))
	exit;
include_once(dirname(__FILE__).'/defines.php');
include_once(dirname(__FILE__).'/classes/Shopimultitab.php');
include_once(dirname(__FILE__).'/classes/ShopimultitabContent.php');
include_once(dirname(__FILE__).'/libs/Helper.php');
class ShopiMultiTabs extends Module
{
	private $_html = '';
	public $base_config_url;
	public $tabcontent;
	
	public function __construct()
	{
		$this->name = 'shopimultitabs';
		$this->tab = 'front_office_features';
		$this->version = '2.0.0';
		$this->author = 'ShopiTheme';
		$this->module_key = '33030b8b28d53f3fb601c9119e72cf51';
		$this->secure_key = Tools::encrypt($this->name);
		$this->bootstrap = true;

		parent::__construct();
		$this->base_config_url = AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getValue('token');
		$this->displayName = $this->l('Shopi Multiple Tabs In Product Module');
		$this->description = $this->l('Shopi Multiple Product Tab Module');
	}
	
	public function install()
	{
		/* Adds Module */
		if (parent::install() && $this->registerHook('displayBackOfficeFooter') && $this->registerHook('actionProductSave') && $this->registerHook('actionProductDelete')
			&& $this->registerHook('displayBackOfficeHeader')
			&& $this->registerHook('displayProductTab') && $this->registerHook('displayProductTabContent') && $this->_installTradDone())
		{
			

			$shops = Shop::getContextListShopID();
			$shop_groups_list = array();

			/* Setup each shop */
			foreach ($shops as $shop_id)
			{
				$shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

				if (!in_array($shop_group_id, $shop_groups_list))
					$shop_groups_list[] = $shop_group_id;

				/* Sets up configuration */
				Configuration::updateValue('SHOPITYPE_TAB', '16', false, $shop_group_id, $shop_id);
				
			}

			/* Sets up Shop Group configuration */
			if (count($shop_groups_list))
				foreach ($shop_groups_list as $shop_group_id)
					Configuration::updateValue('SHOPITYPE_TAB', '16', false, $shop_group_id);

			/* Sets up Global configuration */
			Configuration::updateValue('SHOPITYPE_TAB', '16');

			return true;
		}
		return false;
	}
	
	private function _installTradDone()
	{
		$query = '';
		require_once(dirname(__FILE__).'/install/sql.tables.php');
	 	$error = true;
		if (isset($query) && !empty($query))
		{
			if (!($data=Db::getInstance()->ExecuteS( "SHOW TABLES LIKE '"._DB_PREFIX_."shopimultitab'" )))
			{
				$query = str_replace( '_DB_PREFIX_', _DB_PREFIX_, $query );
				$query = str_replace( '_MYSQL_ENGINE_', _MYSQL_ENGINE_, $query );
				$db_data_settings = preg_split("/;\s*[\r\n]+/",$query);
				foreach ($db_data_settings as $query)
				{
					$query = trim($query);
					if (!empty($query))
						if (!Db::getInstance()->Execute($query))
							$error = false;
				}
			}
		} 
		return $error;
	}
	/**
	 * @see Module::uninstall()
	 */
	public function uninstall()
	{
		/* Deletes Module */
		if (parent::uninstall())
			return true;
		return false;
	}

	public function getContent()
	{
		$errors = array();
		$this->_html .= $this->headerHTML();
		$this->_html .= '<h2>'.$this->displayName.'.</h2>';
		$shop_context = Shop::getContext();
		if (Tools::isSubmit('submitSettings'))
		{
			$shop_groups_list = array();
			$shops = Shop::getContextListShopID();

			foreach ($shops as $shop_id)
			{
				$shop_group_id = (int)Shop::getGroupFromShop($shop_id, true);

				if (!in_array($shop_group_id, $shop_groups_list))
					$shop_groups_list[] = $shop_group_id;

				$res = Configuration::updateValue('SHOPITYPE_TAB', (int)Tools::getValue('SHOPITYPE_TAB'), false, $shop_group_id, $shop_id);
			}

			/* Update global shop context if needed*/
			switch ($shop_context)
			{
				case Shop::CONTEXT_ALL:
					$res = Configuration::updateValue('SHOPITYPE_TAB', (int)Tools::getValue('SHOPITYPE_TAB'));
					if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
							$res = Configuration::updateValue('SHOPITYPE_TAB', (int)Tools::getValue('SHOPITYPE_TAB'), false, $shop_group_id);
					}
					break;
				case Shop::CONTEXT_GROUP:
					if (count($shop_groups_list))
					{
						foreach ($shop_groups_list as $shop_group_id)
							$res = Configuration::updateValue('SHOPITYPE_TAB', (int)Tools::getValue('SHOPITYPE_TAB'), false, $shop_group_id);
					}
					break;
			}

		}
		if (Tools::isSubmit('submitSaveTab'))
		{
			$id_default_language = (int)Configuration::get('PS_LANG_DEFAULT');
			$title_default = Tools::getValue('title_'.$id_default_language);
			if ($title_default)
				if (Tools::getValue('id_shopimultitab'))
				{
					if (Validate::isLoadedObject($obj = new Shopimultitab((int)Tools::getValue('id_shopimultitab'))))
					{
						$obj->active = Tools::getValue('active_tab');
						$languages = Language::getLanguages(false);
						foreach ($languages as $language)
							if (Tools::getValue('title_'.$language['id_lang']) != '')
								$obj->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
						if (!$obj->update())
							$errors[] = $this->l('Tabs could not be updated');
					}
					else
						$errors[] = $this->l('Tabs could not be updated');
				}
				else
				{
					$obj = new Shopimultitab();
					$obj->active = Tools::getValue('active_tab');
					$languages = Language::getLanguages(false);
					foreach ($languages as $language)
						if (Tools::getValue('title_'.$language['id_lang']) != '')
							$obj->title[$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
					if (!$obj->add())
						$errors[] = $this->l('Tabs could not be create');
				}
			else
				$errors[] = $this->l('You have to enter title for default language');

			if (count($errors))
				$this->_html .= $this->displayError(implode('<br />', $errors));
			else
				Tools::redirectAdmin($this->base_config_url);
		}
		elseif (Tools::getIsset('changeStatus'))
		{
			$obj = new Shopimultitab((int)Tools::getValue('id_shopimultitab'));
			if ($obj->active == 0)
				$obj->active = 1;
			else
				$obj->active = 0;
			$res = $obj->update();
			
			$this->_html .= ($res ? $this->displayConfirmation($this->l('Status updated')) : $this->displayError($this->l('Status could not be updated')));
		}
		elseif (Tools::getIsset('deleteTab'))
		{
			$obj = new Shopimultitab((int)Tools::getValue('id_shopimultitab'));
			$res = $obj->delete();
			if (!$res)
				$this->_html .= $this->displayError('Could not delete');
			else
				$this->_html .= $this->displayConfirmation($this->l('Tab deleted'));
		}
		if (Tools::getIsset('addTab'))
			$this->_html .= $this->_displayForm();
		else
		{
			$this->_html .= $this->renderForm();
			$this->_html .= $this->_displayList();
		}

		return $this->_html;
	}
	
	public function contentTabs($id_product)
	{
		$str = '';
		require_once ( dirname(__FILE__).'/form.php' );
		
		return $str;
	}
	
	public function strHook($module_name)
	{
		$hooks = HelperShopiMultiTab::getHooksByModuleName( $module_name );
		$options = '';
		if (!empty($hooks))
			foreach ($hooks as $hook)
				$options .= '<option value="'.$hook['name'].'">'.$hook['name'].'</option>';
		
		return $options;
	}
	
	public function renderForm()
	{
		$this->context->controller->addJS( ($this->_path).'views/js/config.js' );
		$types = array(
			0 => array('value' => '15', 'name' => $this->l('Like default tabs in PrestaShop 1.5')),
            1 => array('value' => '16', 'name' => $this->l('Like default tabs in PrestaShop 1.6')),
		);
		$img15 = _MODULE_DIR_.$this->name.'/views/img/15.png';
		$img16 = _MODULE_DIR_.$this->name.'/views/img/16.png';
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
                        'type' => 'select',
                        'label' => $this->l('Type of tabs:'),
                        'name' => 'SHOPITYPE_TAB',
                        'options' => array(
                            'query' => $types,
                            'id' => 'value',
                            'name' => 'name'
                        ),
                        'desc' => '<br><div class="shopitype shopitype_15"><img src="'.$img15.'" alt="15"/></div>
                        		<div class="shopitype shopitype_16"><img src="'.$img16.'" alt="16"/></div>'
                    )
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();

		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitSettings';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		$id_shop_group = Shop::getContextShopGroupID();
		$id_shop = Shop::getContextShopID();

		return array(
			'SHOPITYPE_TAB' => Tools::getValue('SHOPITYPE_TAB', Configuration::get('SHOPITYPE_TAB', null, $id_shop_group, $id_shop)),
		);
	}

	public function _displayList()
	{
		$obj = new Shopimultitab();
		$tabs = $obj->getTabs();
		foreach ($tabs as $key => $slide)
			$tabs[$key]['status'] = $this->displayStatus($slide['id_shopimultitab'], $slide['active']);

		$this->context->smarty->assign(
			array(
				'link' => $this->context->link,
				'tabs' => $tabs
			)
		);

		return $this->display(__FILE__, 'list.tpl');
	}

	public function _displayForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Tab information'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Tab Title'),
						'name' => 'title',
						'lang' => true,
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Enabled'),
						'name' => 'active_tab',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'active_on',
								'value' => 1,
								'label' => $this->l('Yes')
							),
							array(
								'id' => 'active_off',
								'value' => 0,
								'label' => $this->l('No')
							)
						),
					),
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		if (Tools::isSubmit('id_shopimultitab') && $this->slideExists((int)Tools::getValue('id_shopimultitab')))
		{
			$slide = new Shopimultitab((int)Tools::getValue('id_shopimultitab'));
			$fields_form['form']['input'][] = array('type' => 'hidden', 'name' => 'id_shopimultitab');
		}

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table = $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->module = $this;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitSaveTab';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->tpl_vars = array(
			'base_url' => $this->context->shop->getBaseURL(),
			'language' => array(
				'id_lang' => $language->id,
				'iso_code' => $language->iso_code
			),
			'fields_value' => $this->getAddFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function displayStatus($id_shopimultitab, $active)
	{
		$title = ((int)$active == 0 ? $this->l('Disabled') : $this->l('Enabled'));
		$img = ((int)$active == 0 ? 'disabled.gif' : 'enabled.gif');
		$html = '<a href="'.AdminController::$currentIndex.
				'&configure='.$this->name.'
				&token='.Tools::getAdminTokenLite('AdminModules').'
				&changeStatus&id_shopimultitab='.(int)$id_shopimultitab.'" title="'.$title.'"><img src="'._PS_ADMIN_IMG_.''.$img.'" alt="" /></a>';
		return $html;
	}

	public function getAddFieldsValues()
	{
		$fields = array();

		if (Tools::isSubmit('id_shopimultitab') && $this->slideExists((int)Tools::getValue('id_shopimultitab')))
		{
			$slide = new Shopimultitab((int)Tools::getValue('id_shopimultitab'));
			$fields['id_shopimultitab'] = (int)Tools::getValue('id_shopimultitab', $slide->id);
		}
		else
			$slide = new Shopimultitab();

		$fields['active_tab'] = Tools::getValue('active_tab', $slide->active);
		$fields['has_picture'] = true;

		$languages = Language::getLanguages(false);

		foreach ($languages as $lang)
			$fields['title'][$lang['id_lang']] = Tools::getValue('title_'.(int)$lang['id_lang'], $slide->title[$lang['id_lang']]);

		return $fields;
	}

	public function slideExists($id_shopimultitab)
	{
		$req = 'SELECT hs.`id_shopimultitab`
				FROM `'._DB_PREFIX_.'shopimultitab` hs
				WHERE hs.`id_shopimultitab` = '.(int)$id_shopimultitab;
		$row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($req);

		return ($row);
	}
	
	public function headerHTML()
	{
		if (Tools::getValue('controller') != 'AdminModules' && Tools::getValue('configure') != $this->name)
			return;

		$this->context->controller->addJqueryUI('ui.sortable');
		/* Style & js for fieldset 'slides configuration' */
		$html = '<script type="text/javascript">
			$(function() {
				var $myTabs = $("#tabs");
				$myTabs.sortable({
					opacity: 0.6,
					cursor: "move",
					update: function() {
						var order = $(this).sortable("serialize") + "&task=updatePosition";
						$.post("'.$this->context->shop->physical_uri.$this->context->shop->virtual_uri.'modules/'.$this->name.'/ajax.php?secure_key='.$this->secure_key.'", order);
						}
					});
				$myTabs.hover(function() {
					$(this).css("cursor","move");
					},
					function() {
					$(this).css("cursor","auto");
				});
			});
		</script>';

		return $html;
	}

	public function hookdisplayBackOfficeHeader()
	{
		return '<link href="'._MODULE_DIR_.$this->name.'/views/css/admin.css" rel="stylesheet" type="text/css" media="all" />';
	}
	
	public function hookactionProductSave($params)
	{
		$tabs = Shopimultitab::getTabs();
		$languages = Language::getLanguages(false);
		$content = array();
		if (!$tabs)
			return true;
		foreach ($tabs as $tab)
		{
			$content_text = array();
			foreach ($languages as $lang)
				$content_text[$lang['id_lang']] = Tools::getValue('shopicontent_text_'.$tab['id_shopimultitab'].'_'.$lang['id_lang']);
			
			if (Tools::getValue('id_shopimultitab_content_'.$tab['id_shopimultitab']))
				$obj = new ShopimultitabContent((int)Tools::getValue('id_shopimultitab_content_'.$tab['id_shopimultitab']));
			else
				$obj = new ShopimultitabContent();
			$obj->id_shopimultitab = $tab['id_shopimultitab'];
			$obj->id_product = $params['id_product'];
			$obj->content_text = $content_text;
			$obj->categories = Tools::getValue('categories_'.$tab['id_shopimultitab']);
			$obj->global = (int)Tools::getValue('global_'.$tab['id_shopimultitab']);

			$shopimodule = Tools::getValue('shopimodule_'.$tab['id_shopimultitab'], '');
			$shopihook = Tools::getValue('shopihook_'.$tab['id_shopimultitab'], '');
			if ($shopimodule && $shopihook)
				$obj->content = $shopimodule.':'.$shopihook;
			
			if (Tools::getValue('id_shopimultitab_content_'.$tab['id_shopimultitab']))
				$obj->update();
			else
				if (!(ShopimultitabContent::checkExist( $tab['id_shopimultitab'], $params['id_product'] )))
					$obj->add();
		}
	}
	
	public function hookactionProductDelete($params)
	{
		if (!$params['id_product'])
			return '';
		$shopicontent = ShopimultitabContent::getContents(null, $params['id_product']);
		if (!$shopicontent)
			return '';
		foreach ($shopicontent as $content)
		{
			$obj = new ShopimultitabContent($content['id_shopimultitab_content']);
			$obj->delete();
		}
	}
	
	public function hookdisplayBackOfficeFooter()
	{
		$this->context->smarty->assign(array(
			'back_url' => _MODULE_DIR_.$this->name.'/ajax.php?secure_key='.$this->secure_key,
			'shopi_id_product' => (int)Tools::getValue('id_product')
		));
		if (Tools::strtolower(Tools::getValue('controller')) == Tools::strtolower('adminproducts'))
			return $this->display(__FILE__, 'backofficefooter.tpl' );
		return '';
	}

	public function getGlobalContent($global_contents, $categories)
	{
		if ($global_contents)
		{
			foreach ($global_contents as $global_content)
			{
				$gcategories = $global_content['categories'];
				if ($gcategories)
				{
					$gcategories = explode(',', $gcategories);
					foreach ($gcategories as $id_category)
						if (in_array($id_category, $categories))
							return $global_content;
				}
			}
		}
		return false;
	}

	public function hookdisplayProductTab()
	{
		if (!$this->tabcontent)
		{
			$id_product = (int)(Tools::getValue('id_product'));
			$tabs = Shopimultitab::getTabs( true );
			if (!$tabs)
				return '';
			foreach ($tabs as &$tab)
			{
				$categories = Product::getProductCategories($id_product);
				$global_contents = ShopimultitabContent::getContents($tab['id_shopimultitab'], null, true);
				$content = $this->getGlobalContent($global_contents, $categories);
				if (!$content)
					$content = ShopimultitabContent::getContent($tab['id_shopimultitab'], $id_product);
				if ($content)
				{
					$content['module'] = HelperShopiMultiTab::execModule($content['content']);
					if ($content['module'] || $content['content_text'])
						$tab['contenttab'] = $content;
				}
			}
			$this->tabcontent = $tabs;
		}
		if (!$this->tabcontent) 
			return '';
		$this->context->smarty->assign(array(
			'shopimultitabs_tabs' => $this->tabcontent
		));
		
		if (Configuration::get('SHOPITYPE_TAB') == 15)
            return $this->display(__FILE__, 'producttabs.tpl');
        else
            return $this->display(__FILE__, 'producttabs16.tpl');
	}
	
	public function hookdisplayProductTabContent()
	{
		$this->context->controller->addCSS( ($this->_path).'views/css/style.css', 'all' );
		if (!$this->tabcontent)
		{
			$tabs = Shopimultitab::getTabs( true );
			if (!$tabs) 
				return '';
			$id_product = Tools::getValue('id_product');
			foreach ($tabs as &$tab)
			{
				$categories = Product::getProductCategories($id_product);
				$global_contents = ShopimultitabContent::getContents($tab['id_shopimultitab'], null, true);
				$content = $this->getGlobalContent($global_contents, $categories);
				if (!$content)
					$content = ShopimultitabContent::getContent($tab['id_shopimultitab'], $id_product);
				if ($content)
				{
					$content['module'] = HelperShopiMultiTab::execModule($content['content']);
					if ($content['module'] || $content['content_text'])
						$tab['contenttab'] = $content;
				}
			}
			$this->tabcontent = $tabs;
		}
		if (!$this->tabcontent)
			return '';
		$this->context->smarty->assign(array(
			'shopimultitabs_tabs' => $this->tabcontent
		));

		if (Configuration::get('SHOPITYPE_TAB') == 15)
            return $this->display(__FILE__, 'producttabscontent.tpl');
        return '';
	}
	
	
}
