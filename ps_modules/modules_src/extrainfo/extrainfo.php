<?php

class extrainfo extends Module
{
	function __construct()
	{
		$this->name = 'extrainfo';
		$this->tab = 'front_office_features';
		$this->version = 1.0;
		$this->author = 'Caglar';
		$this->need_instance = 0;

		parent::__construct(); // The parent construct is required for translations

		$this->displayName = $this->l('Extra Info');
		$this->description = $this->l('Adds an extra product info tab on product page.<br />(www.cepparca.com | by Caglar)');
	}

	function install()
	{
        if (parent::install() == false 
				OR $this->registerHook('productTab') == false
				OR $this->registerHook('productTabContent') == false)
			return (false);
		return (true);
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/

	function hookProductTab($params)
	{
		global $smarty, $cookie;
		return $this->display(__FILE__, 'extratab.tpl');

	}
    
	public function hookProductTabContent($params)
    {
		global $smarty, $cookie, $link;
		/* Product informations */
		$product = new Product((int)Tools::getValue('id_product'), false, (int)$cookie->id_lang);

		$smarty->assign(array(
			'product' => $product
		));	
		
		return ($this->display(__FILE__, 'extratabContents.tpl'));
	}	

}

?>