{*
 *  Shopi Multiple Tabs In Product Module
 *
 * @author    ShopiTheme;
 * @version   2.0.0
 * @copyright Copyright (C) October 2013 prestabrain.com <@emai:shopitheme@gmail.com>;
 * @license   GNU General Public License version 2;
*}

<script type="text/javascript">
	jQuery(document).ready(function(){
		$.ajax({
			type: "POST",
			url: "{$back_url|escape:'html':'UTF-8'}",
			data: "task=tab&id_product={$shopi_id_product|intval}",
			success: function(data){
				$('.productTabs .list-group').append(data);
			}
		});
		$.ajax({
			type: "POST",
			url: "{$back_url|escape:'html':'UTF-8'}",
			data: "task=content&id_product={$shopi_id_product|intval}",
			success: function(data){
				$('#product_form').append(data);
			}
		});
		
	});
</script>

	