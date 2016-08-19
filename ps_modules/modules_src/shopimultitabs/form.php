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
	
	$tabs = Shopimultitab::getTabs();
	$types = array('html' => 'Html', 'module' => 'Module');
	$str = '';
	if ($tabs)
	{
		$id_product = Tools::getValue('id_product');
		foreach ($tabs as $tab)
		{
			$tabContent = ShopimultitabContent::getContent($tab['id_shopimultitab'], $id_product);
			$str .= '<div id="product-tab-content-shopi-tab-'.$tab['id_shopimultitab'].'" class="product-tab-content shopi-product-tab-content panel" style="display:none">';
			if ($tabContent)
				$obj = new ShopimultitabContent($tabContent['id_shopimultitab_content']);
			else
				$obj = new ShopimultitabContent();
				
			
			$str .= '
			<div class="form-group">
			<label class="control-label col-lg-3">Content Text:</label>
			<div class="col-lg-9">';
			$languages = Language::getLanguages(false);
			$id_lang_default = (int)Configuration::get('PS_LANG_DEFAULT');
			foreach ($languages as $language)
			{
				$str .= '
				<div class="translatable-field row lang-'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $id_lang_default ? 'block' : 'none').';">
					<div class="col-lg-9">
						<textarea class="rte" id="shopicontent_text_'.$tab['id_shopimultitab'].'_'.$language['id_lang'].'" name="shopicontent_text_'.$tab['id_shopimultitab'].'_'.$language['id_lang'].'" cols="30" row="5">'.htmlentities((isset($obj->content_text[$language['id_lang']]) ? $obj->content_text[$language['id_lang']] : ''), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';

					$str .= '
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							'.$language['iso_code'].'
							<i class="icon-caret-down"></i>
						</button>
						<ul class="dropdown-menu">';
							foreach ($languages as $lang)
								$str .= '<li><a href="javascript:hideOtherLanguage('.$lang['id_lang'].');" tabindex="-1">'.$lang['name'].'</a></li>';
							
					$str .= '
						</ul>
					</div>';
				$str .= '
				</div>';
			}

			$str .= '</div>';
			$str .= '</div>';
			
			$str .= '
			<div class="form-group">
			<label class="control-label col-lg-3">Modules:</label>
			<div class="col-lg-9">
			';
			/* MODULE */
			/* Submenu Modules */
			$hookoptions = '';
			$shopimodule = '';
			$shopihook = '';
			if ($obj->content)
			{
				$arrmodulehook = explode(':', $obj->content);
				if (count($arrmodulehook) == 2)
				{
					$shopimodule = $arrmodulehook[0];
					$shopihook = $arrmodulehook[1];
				}
				$hookAssign = HelperShopiMultiTab::getHooksByModuleName($shopimodule);
				foreach ($hookAssign as $hook)
					$hookoptions .= '<option value="'.$hook['name'].'"'.((isset($shopihook) && Tools::strtolower($shopihook) == Tools::strtolower($hook['name'])) ? ' selected="selected"' : '').'>'.$hook['name'].'</option>';    
				
			}
			$modules = HelperShopiMultiTab::getModules();
	        	$options = '';
			foreach ($modules as $module)
				$options .= '<option value="'.$module['name'].'"'.((isset($shopimodule) && $shopimodule == $module['name']) ? ' selected="selected"' : '').'>'.$module['name'].'</option>';    
			
			$str .= '
					<div class="menu_type_modules">
						<div class="shopi-header">
							<input type="hidden" value="'.$obj->id.'" name="id_shopimultitab_content_'.$tab['id_shopimultitab'].'" class="id_shopimultitab_content"/>
							<input type="hidden" value="'.$tab['id_shopimultitab'].'" name="id_shopimultitab_'.$tab['id_shopimultitab'].'" class="id_shopimultitab"/>
							<div class="shopi-left">
								<label class="labelmodule">Modules:</label>
								<select class="shopimodule" name="shopimodule_'.$tab['id_shopimultitab'].'">
									<option value=""> --- Choose module --- </option>'.$options.'
								</select>
							</div>
							<div class="shopi-right">
								<label class="labelhook">Override hooks:</label>
								<select class="shopihook" name="shopihook_'.$tab['id_shopimultitab'].'" id="shopihook-'.$tab['id_shopimultitab'].'">
									<option value=""> --- Choose hook --- </option>'.$hookoptions.'
								</select>
							</div>
						</div>
					</div>';
						
			
			$str .= '</div>';
			$str .= '</div>';

			$str .= '
			<div class="form-group">
			<label class="control-label col-lg-3">Global Block:</label>
			<div class="col-lg-9">';
			$str .= '<input type="checkbox" value="1" name="global_'.$tab['id_shopimultitab'].'" '.($obj->global ? 'checked="checked"' : '').'>';
			$str .= '</div>';
			$str .= '</div>';
			$str .= '
			<div class="form-group">
				<div class="col-lg-9 col-lg-offset-3">
					<div class="alert alert-info">
						This option will allow to display this block on other products that are associated with selected categories (you can select categories below) 
					</div>
				</div>
			</div>
			';
			$str .= '
			<div class="form-group">
			<label class="control-label col-lg-3">Categories ID:</label>
			<div class="col-lg-9">';
			$str .= '<input type="text" name="categories_'.$tab['id_shopimultitab'].'" value="'.$obj->categories.'"> 
				<p>Example: 1,2,3</p>
				';
			$str .= '</div>';
			$str .= '</div>';
			/* END MODULE */
			$str .= '
			<div class="panel-footer">
				<a class="btn btn-default" href="index.php?controller=AdminProducts&amp;token=2f8cb536e23d0570656518a0ad2dd63f"><i class="process-icon-cancel"></i> Cancel</a>
				<button class="btn btn-default pull-right" name="submitAddproduct" type="submit"><i class="process-icon-save"></i> Save</button>
				<button class="btn btn-default pull-right" name="submitAddproductAndStay" type="submit"><i class="process-icon-save"></i> Save and stay</button>
			</div>
			';
			$str .= '</div>';
		}
		
		$iso = Language::getIsoById((int)(Context::getContext()->language->id));
		$isoTinyMCE = (file_exists(_PS_ROOT_DIR_.'/js/tiny_mce/langs/'.$iso.'.js') ? $iso : 'en');
		$adminfolder = HelperShopiMultiTab::getFolderAdmin();
		$ad = __PS_BASE_URI__.$adminfolder;
		$str .= '
		<script type="text/javascript">	
			var iso = \''.$isoTinyMCE.'\' ;
			var pathCSS = \''._THEME_CSS_DIR_.'\' ;
			var ad = \''.$ad.'\' ;
			tinySetup();
		</script>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				$(\'.shopimodule\').change(function(){
					var module_name = $(this).find(\'option:selected\').val();
					var id_shopimultitab = $(this).parent().parent().find(\'.id_shopimultitab\').val();
					if(module_name){
						$.ajax({
							type: "POST",
							url: "'._MODULE_DIR_.'shopimultitabs/ajax.php?secure_key='.Tools::encrypt('shopimultitabs').'",
							data: "module_name="+module_name+"&task=gethook",
							success: function(data){
								select_innerHTML(document.getElementById("shopihook-" + id_shopimultitab),data);
							}
						});
					}
				});
			});
			function select_innerHTML(objeto,innerHTML){
				objeto.innerHTML = ""
				var selTemp = document.createElement("micoxselect")
				var opt;
				selTemp.id="micoxselect1"
				document.body.appendChild(selTemp)
				selTemp = document.getElementById("micoxselect1")
				selTemp.style.display="none"
				if(innerHTML.toLowerCase().indexOf("<option")<0){//se não é option eu converto
					innerHTML = "<option>" + innerHTML + "</option>"
				}
				innerHTML = innerHTML.toLowerCase().replace(/<option/g,"<span").replace(/<\/option/g,"</span")
				selTemp.innerHTML = innerHTML
				
				for(var i=0;i<selTemp.childNodes.length;i++){
			  var spantemp = selTemp.childNodes[i];
			  
					if(spantemp.tagName){     
						opt = document.createElement("OPTION")
				
			   if(document.all){ //IE
				objeto.add(opt)
			   }else{
				objeto.appendChild(opt)
			   }
			   //getting attributes
			   for(var j=0; j<spantemp.attributes.length ; j++){
				var attrName = spantemp.attributes[j].nodeName;
				var attrVal = spantemp.attributes[j].nodeValue;
				if(attrVal){
				 try{
				  opt.setAttribute(attrName,attrVal);
				  opt.setAttributeNode(spantemp.attributes[j].cloneNode(true));
				 }catch(e){}
				}
			   }
			   //getting styles
			   /*
			   if(spantemp.style){
				for(var y in spantemp.style){
				 try{opt.style[y] = spantemp.style[y];}catch(e){}
				}
			   }
			   */
			   //value and text
			   opt.value = spantemp.getAttribute("value")
			   opt.text = spantemp.innerHTML
			   //IE
			   opt.selected = spantemp.getAttribute(\'selected\');
			   //opt.className = spantemp.className;
			  }
			 }
			 document.body.removeChild(selTemp)
			 selTemp = null
			}
		</script>';
	}