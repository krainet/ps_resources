{*
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @version   2.0.0
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
*}

<div class="panel"><h3><i class="icon-list-ul"></i> {l s='Tabs list' mod='shopimultitabs'}
	<span class="panel-heading-action">
		<a id="desc-product-new" class="list-toolbar-btn" href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=shopimultitabs&addTab=1">
			<span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="Add new" data-html="true">
				<i class="process-icon-new "></i>
			</span>
		</a>
	</span>
	</h3>
	<div id="tabsContent">
		<div id="tabs">
			{foreach from=$tabs item=tab}
				<div id="tabs_{$tab.id_shopimultitab|intval}" class="panel">
					<div class="row">
						<div class="col-lg-1">
							<span><i class="icon-arrows "></i></span>
						</div>
						
						<div class="col-md-11">
							<h4 class="pull-left">
								#{$tab.id_shopimultitab|intval} - {$tab.title|escape:'html':'UTF-8'}
							</h4>
							<div class="btn-group-action pull-right">
								{$tab.status}
								
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=shopimultitabs&id_shopimultitab={$tab.id_shopimultitab}&addTab=1">
									<i class="icon-edit"></i>
									{l s='Edit' mod='shopimultitabs'}
								</a>
								<a class="btn btn-default"
									href="{$link->getAdminLink('AdminModules')|escape:'html':'UTF-8'}&configure=shopimultitabs&delete_id_tab={$tab.id_shopimultitab|intval}">
									<i class="icon-trash"></i>
									{l s='Delete' mod='shopimultitabs'}
								</a>
							</div>
						</div>
					</div>
				</div>
			{/foreach}
		</div>
	</div>
</div>