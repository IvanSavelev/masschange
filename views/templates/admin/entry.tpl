{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2017 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{if isset($message)}{$message|escape:'quotes':'UTF-8'}{/if}

<div class="panel">
<h3><i class="icon-cogs"></i> {l s='Products' mod='masschange'} <span class="badge">{$products|@count|escape:'html':'UTF-8'} </span></h3>

<div class="row">
    <div class=" col-xs-12">
        <h4 class="page-subheading">{l s='Categories' mod='masschange'}</h4>

        <div class="tree_top">
            <a href="{$current_url|escape:'htmlall':'UTF-8'}&amp;
                id_category={$categoriesTree.id|escape:'quotes':'UTF-8'}&amp;open_category=1"
               title="{$categoriesTree.name|escape:'html':'UTF-8'}"
                    {if $id_category == $categoriesTree.id || $id_category == 2 || $id_category == 0} id="category_check" autofocus="autofocus" {/if}>{$categoriesTree.name|escape:'html':'UTF-8'}</a>
        </div>
        <ul class="tree">
            {if isset($categoriesTree.children)}
                {foreach $categoriesTree.children as $child}
                    {if $child@last}
                        {include file=$modules_dir node=$child last='true' id_category=$id_category}
                    {else}
                        {include file=$modules_dir node=$child id_category=$id_category}
                    {/if}
                {/foreach}
            {/if}
        </ul>
    </div>
</div>

<br>


<form method="post" action="{$current_url|escape:'htmlall':'UTF-8'}" id="massive_form">

    <!-- PRICE MODIFIER -->
    <div class="well">
        <div class="row form-group">
            <div class="col-sm-1 col-lg-1">  <!-- TODO -->
                <select name="massive_price_factor_prefix" class="form-control">
                    <option value="+" selected="selected">+</option>
                    <option value="-">-</option>
                    <option value="*">*</option>
                    <option value="=">=</option>
                </select>
            </div>
            <div class="col-sm-5 col-lg-6">
                <input type="text" name="massive_price_factor" value="" pattern="\d+(\.\d*)?" placeholder="{l s='Change the price in the selected products' mod='masschange'}" class="form-control">
            </div>
            <div class="col-sm-3 col-lg-2">
                <button type="submit"
                        title="{l s='Changes will immediately take effect' mod='masschange'}"
                        name="massive_price_button"
                        class="btn btn-default">
                    <i class="icon-bolt text-success"></i> {l s='Apply' mod='masschange'}
                </button>
            </div>

            <div class="col-sm-3 col-lg-3">
                <span>{l s='Change the price in the selected products' mod='masschange'}</span>
            </div>
        </div>
    </div>


    <!-- QUANTITY MODIFIER -->
    <div class="well">
        <div class="row form-group">
            <div class="col-sm-1 col-lg-1">
                <select name="massive_quantity_factor_prefix" class="form-control">
                    <option value="+" selected="selected">+</option>
                    <option value="-">-</option>
                    <option value="*">*</option>
                    <option value="=">=</option>
                </select>
            </div>
            <div class="col-sm-5 col-lg-6">
                <input type="text" name="massive_quantity_factor" value="" pattern="\d+(\.\d*)?" placeholder="{l s='Change the quantity in the selected products' mod='masschange'}" class="form-control">
            </div>
            <div class="col-sm-3 col-lg-2">
                <button type="submit"
                        title="{l s='Changes will immediately take effect' mod='masschange'}"
                        name="massive_quantity_button"
                        class="btn btn-default">
                    <i class="icon-bolt text-success"></i> {l s='Apply' mod='masschange'}
                </button>
            </div>

            <div class="col-sm-3 col-lg-3">
                <span>{l s='Change the quantity in the selected products' mod='masschange'}</span>
            </div>
        </div>
    </div>

    <!-- ACTIVE AND TAX MODIFIER -->
    <div class="well">
        <div class="row">
            <div class="col-sm-4 col-lg-4 row form-group">
                <button type="submit"
                        title="{l s='Activate all selected products' mod='masschange'}"
                        name="massive_active_on_button"
                        class="btn btn-default">
                    <i class="icon-power-off text-success"></i> {l s='Activate' mod='masschange'}
                </button>
                <button type="submit"
                        title="{l s='Deactivate all selected products' mod='masschange'}"
                        name="massive_active_off_button"
                        class="btn btn-default">
                    <i class="icon-power-off text-danger"></i> {l s='Deactive' mod='masschange'}
                </button>
            </div>

            <div class="col-sm-5 col-lg-5 row form-group">
                <select name="massive_id_tax_rules_group" class="col-sm-4">
                    <option value="0" selected="selected">{l s='No Tax' mod='masschange'}</option>
                    {foreach from=$tax_rules_groups item=tax_rules_group}
                        <option {if $tax_exclude_tax_option}disabled="disabled" {/if}
                                value="{$tax_rules_group['id_tax_rules_group']|escape:'quotes':'UTF-8'}">
                            {$tax_rules_group['name']|escape:'quotes':'UTF-8'}
                        </option>
                    {/foreach}
                </select>
                <div class="col-sm-8">
                    <button type="submit"
                            title="{l s='Apply tax for all selected products' mod='masschange'}"
                            name="massive_tax_button"
                            class="btn btn-default">
                        <i class="icon-dashboard text-success"></i> {l s='Tax for all products' mod='masschange'}
                    </button>
                </div>
            </div>

            <div class="col-sm-3 col-lg-3 row form-group">
                <button type="submit"
                        title="{l s='Delete the specific price for all selected products' mod='masschange'}"
                        name="delete_specific_price_button_massive"
                        class="btn btn-default">
                    <i class="icon-trash text-danger"></i> {l s='Delete specific price' mod='masschange'}
                </button>
            </div>
        </div>
    </div>

	<!-- CHOICE OF COLUMNS -->
	<div class="well">
		<div class="row">
			<div class="col-sm-4 col-lg-4 row form-group">
				<button type="submit"
						title="{l s='Activate all selected products' mod='masschange'}"
						name="massive_active_on_button"
						class="btn btn-default">
					<i class="icon-power-off text-success"></i> {l s='Activate' mod='masschange'}
				</button>
			</div>

			<div class="col-sm-5 col-lg-5 row form-group">
				<select name="massive_id_tax_rules_group" class="col-sm-4">
					<option value="0" selected="selected">{l s='No Tax' mod='masschange'}</option>
					{foreach from=$tax_rules_groups item=tax_rules_group}
						<option {if $tax_exclude_tax_option}disabled="disabled" {/if}
								value="{$tax_rules_group['id_tax_rules_group']|escape:'quotes':'UTF-8'}">
							{$tax_rules_group['name']|escape:'quotes':'UTF-8'}
						</option>
					{/foreach}
				</select>
			</div>
		</div>
	</div>

</form>



<form method="post" action="{$current_url|escape:'htmlall':'UTF-8'}" class="form-horizontal clearfix" id="form-product" style="display:inline-block;  width: 1300px;   overflow-x: scroll;">
<input type="hidden" name="save_all" value="1"/>
<div class="row" style="width:3000px;">

<table class="table" style="border: 5px double #000;">
<thead>
<tr>
    <th class="fixed-width-xs center"></th>

    <th class="fixed-width-xs center">
			<span class="title_box active">{l s='ID' mod='masschange'}
                <a {if ($filter_name == 'id' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Id&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'id' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Id&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

    <th class="pointer center">
        <span class="title_box">{l s='Image' mod='masschange'}</span>
    </th>

    <th class="">
			<span class="title_box center">{l s='Name' mod='masschange'}
                <a {if ($filter_name == 'name' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Name&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'name' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Name&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

    <th class="">
			<span class="title_box center">{l s='Reference' mod='masschange'}
                <a {if ($filter_name == 'reference' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Reference&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'reference' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Reference&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

    <th class="">
			<span class="title_box">{l s='Category' mod='masschange'}
                <a {if ($filter_name == 'category' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Category&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'category' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Category&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

	<th class="center">
		<span class="">{l s='Summary' mod='masschange'}</span>
	</th>

	<th class="center">
		<span class="">{l s='Description' mod='masschange'}</span>
	</th>

	<th class="center">
		<span class="">{l s='Meta title' mod='masschange'}</span>
	</th>

	<th class="center">
		<span class="">{l s='Meta description' mod='masschange'}</span>
	</th>

    <th class=" text-right">
			<span class="title_box">{l s='Base price' mod='masschange'}
                <a {if ($filter_name == 'base_price' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Base_price&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'base_price' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Base_price&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

    <th class="text-center">
        <span class="title_box">{l s='Final price' mod='masschange'}</span>
    </th>

    <th class="text-center">
			<span class="title_box">{l s='Quantity' mod='masschange'}
                <a {if ($filter_name == 'quantity' && $filter_order == 'desc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Quantity&amp;filter_order=desc">
                    <i class="icon-caret-down"></i>
                </a>
				<a {if ($filter_name == 'quantity' && $filter_order == 'asc')}class="active"{/if} href="{$current_url|escape:'htmlall':'UTF-8'}&amp;filter_name=Quantity&amp;filter_order=asc">
                    <i class="icon-caret-up"></i>
                </a>
			</span>
    </th>

    <th class="fixed-width-sm text-center">
        <span class="title_box">{l s='Status' mod='masschange'}</span>
    </th>

    <th>
    </th>

</tr>



<tr class="nodrag nodrop filter row_hover">

    <th class="center">
        --
    </th>

    <th class="center">
        <input type="text" class="filter" pattern="^[\-0-9]+$" name="find_data[id]" value="{if isset($find_data['id'])}{$find_data['id']|escape:'html':'UTF-8'}{/if}">
    </th>

    <th class="center">
        --
    </th>

    <th>
        <input type="text" class="filter" name="find_data[name]" value="{if isset($find_data['name'])}{$find_data['name']|escape:'html':'UTF-8'}{/if}">
    </th>

    <th class="left">
        <input type="text" class="filter" name="find_data[reference]" value="{if isset($find_data['reference'])}{$find_data['reference']|escape:'html':'UTF-8'}{/if}">
    </th>

    <th class="text-right">
        <input type="text" class="filter" name="find_data[category]" value="{if isset($find_data['category'])}{$find_data['category']|escape:'html':'UTF-8'}{/if}">
    </th>

	<th class="center">
		--
	</th>

	<th class="center">
		--
	</th>

	<th class="center">
		--
	</th>

	<th class="center">
		--
	</th>

    <th class="text-right">
        <input type="text" class="filter" pattern="\d+(\.\d*)?" name="find_data[base_price]" value="{if isset($find_data['base_price'])}{$find_data['base_price']|escape:'html':'UTF-8'}{/if}">
    </th>

    <th class="center">
        --
    </th>

    <th class="text-right">
        <input type="text" class="filter" pattern="^[\-0-9]+$" name="find_data[quantity]" value="{if isset($find_data['quantity'])}{$find_data['quantity']|escape:'html':'UTF-8'}{/if}">
    </th>

    <th class="text-center">
        <select class="filter fixed-width-sm center" name="find_data[active]">
            <option value="">-</option>
            <option value="1" {if isset($find_data['active']) && ($find_data['active'] === 1)}selected{/if}>{l s='Yes' mod='masschange'}</option>
            <option value="0" {if isset($find_data['active']) && ($find_data['active'] === 0)}selected{/if}>{l s='No' mod='masschange'}</option>
        </select>
    </th>

    <th class="actions">
			<span class="pull-right">
				<button type="submit" name="submitFilterMassChange" class="btn btn-default">
                    <i class="icon-search"></i> {l s='Find' mod='masschange'}
                </button>
                {if isset($find_data) && !$find_data|@count == 0}
                    <button type="submit" name="submitResetProductMassChange" class="btn btn-warning">
                        <i class="icon-eraser"></i>{l s='Reset' mod='masschange'}
                    </button>
                {/if}
			</span>
    </th>
</tr>

</thead>

{if $products|count > 0 && $products != null}
    <tbody>
    {foreach from=$products key=k item=product}
        <tr class="">

            <input type="hidden" name="id_product[]" value="{$product['id_product']|escape:'quotes':'UTF-8'}" />

            <td class="text-center">
                <input type="checkbox" name="selected_id_product[]"
                       value="{$product['id_product']|escape:'quotes':'UTF-8'}"
                       class="noborder">
            </td>

            <td class="fixed-width-xs center">{$product['id_product']|escape:'html':'UTF-8'}</td>

            <td class="pointer center"><img class="imgm img-thumbnail"
                                            src="{$product.image_dir|escape:'htmlall':'UTF-8'}" alt="no image"
                                            width="45px">
            </td>
            <td class="left">{$product['name']|escape:'html':'UTF-8'}</td>

            <td class="left">{$product['reference']|escape:'html':'UTF-8'}</td>

            <td class="pointer text-left">{$product['name_category']|escape:'html':'UTF-8'}</td>



			<td class="pointer  text-right">
				<textarea class="" name="description_short[{$product['id_product']|escape:'html':'UTF-8'}]">{strip_tags($product['description_short']|escape:'quotes':'UTF-8')}</textarea>
			</td>

			<td class="pointer  text-right">
				<textarea class="" name="description[{$product['id_product']|escape:'html':'UTF-8'}]">{strip_tags($product['description'])}</textarea>
			</td>

			<td class="pointer  text-right">
				<input type="text" class="" name="meta_title[{$product['id_product']|escape:'html':'UTF-8'}]" value="{strip_tags($product['meta_title']|escape:'html':'UTF-8')}">
			</td>

			<td class="pointer  text-right">
				<input type="text" class="" name="meta_description[{$product['id_product']|escape:'html':'UTF-8'}]" value="{strip_tags($product['meta_description']|escape:'html':'UTF-8')}">
			</td>



            <td class="pointer fixed-width-md text-right">
                <div class="input-group">
                    {if $product['price_prefix_left'] != "" }<span class="input-group-addon">{$product['price_prefix_left']|escape:'html':'UTF-8'}</span>{/if}
                    <input type="text" pattern="\d+(\.\d*)?" class="" name="base_price[{$product['id_product']|escape:'html':'UTF-8'}]" value="{$product['price']|escape:'html':'UTF-8'}">
                    {if $product['price_prefix_right'] != "" }<span class="input-group-addon">{$product['price_prefix_right']|escape:'html':'UTF-8'}</span>{/if}
                </div>
            </td>

            <td class="final_price pointer text-left text-right">
                {foreach from=$product['specific_price'] key=k item=specific_price}
                    <div class="specific_price_box pull-right">
                        {$check = 0}
                        {if $specific_price['iso_code'] != null}
                            <p class="text-left">
                                <label>{l s='For: ' mod='masschange'}</label>
                                <span>{l s='currency ' mod='masschange'} {$specific_price['iso_code']|escape:'html':'UTF-8'}</span>
                            </p>
                            {$check = 1}
                        {/if}
                        {if $specific_price['country_name'] != null}
                            <p class="text-left">
                                <label>{l s='For: ' mod='masschange'}</label>
                                <span>{l s='country ' mod='masschange'} {$specific_price['country_name']|escape:'html':'UTF-8'}</span>
                            </p>
                            {$check = 1}
                        {/if}
                        {if $specific_price['group_name'] != null}
                            <p class="text-left">
                                <label>{l s='For: ' mod='masschange'}</label>
                                <span>{l s='group ' mod='masschange'} {$specific_price['group_name']|escape:'html':'UTF-8'}</span>
                            </p>
                            {$check = 1}
                        {/if}
                        {if $specific_price['from_quantity'] > 1}
                            <p class="text-left">
                                <label>{l s='For: ' mod='masschange'}</label>
                                <span>{l s='quantity ' mod='masschange'} {$specific_price['from_quantity']|escape:'html':'UTF-8'}</span>
                            </p>
                            {$check = 1}
                        {/if}
                        {if $specific_price['from'] > 1 || $specific_price['to'] > 1}
                            <p class="text-left">
                                <label>{l s='For: ' mod='masschange'}</label>
                                {if $specific_price['from'] > 1 && $specific_price['to'] > 1}
                                    <span>{l s='From ' mod='masschange'}{$specific_price['from']|escape:'html':'UTF-8'}{l s=' - ' mod='masschange'}{$specific_price['to']|escape:'html':'UTF-8'}</span>
                                {/if}
                                {if $specific_price['from'] > 1 && $specific_price['to'] == 0}
                                    <span>{l s='From ' mod='masschange'} {$specific_price['from']|escape:'html':'UTF-8'}</span>
                                {/if}
                                {if $specific_price['from'] == 0 && $specific_price['to'] > 1}
                                    <span>{l s='To ' mod='masschange'} {$specific_price['to']|escape:'html':'UTF-8'}</span>
                                {/if}

                            </p>
                            {$check = 1}
                        {/if}
                        {if $check == 0}
                            <p class="text-left">
                                <label>{l s='General discount' mod='masschange'}</label>
                            </p>
                        {/if}

                        <div class="input-group">
                            <input type="hidden" name="id_specific_price_delete[{$specific_price['id_specific_price']|escape:'html':'UTF-8'}]" value="0" />
                            {if $specific_price['reduction_type'] == 'amount'}{if $specific_price['price_prefix_left'] != "" }<span class="input-group-addon">{$specific_price['price_prefix_left']|escape:'html':'UTF-8'}</span>{/if}{/if}
                            <input  type="text"
                                    pattern="\d+(\.\d*)?"
                                    name="id_specific_price[{$specific_price['id_specific_price']|escape:'html':'UTF-8'}]"
                                    value="{if $specific_price['reduction_type'] == 'amount'}{$specific_price['reduction']|escape:'html':'UTF-8'}{/if}{if $specific_price['reduction_type'] == 'percentage'}{($specific_price['reduction']*100)|escape:'html':'UTF-8'}{/if}">
                            {if $specific_price['reduction_type'] == 'amount'}{if $specific_price['price_prefix_right'] != "" }<span class="input-group-addon" >{$specific_price['price_prefix_right']|escape:'html':'UTF-8'}</span>{/if}{/if}
                            {if $specific_price['reduction_type'] == 'percentage'}<span class="input-group-addon">%</span>{/if}
                            <div class=" btn btn-default pull-left delete_specific_price" title="{l s='Delete' mod='masschange'}">
                                <span><i class="icon-trash"></i></span>
                            </div>

                        </div>

                        {if $specific_price['reduction_tax'] == 1}
                            <small class="text-left">{l s='(On VAT)' mod='masschange'}</small>
                        {else}
                            <small class="text-left">{l s='(Off VAT)' mod='masschange'}</small>
                        {/if}

                    </div>
                    <!-- <br> -->

                {/foreach}

                <div class="btn-group deploy_price">
                    <div class="price_button btn btn-default" title="{l s='Deploy' mod='masschange'}">
                        {$product['final_price']|escape:'html':'UTF-8'}
                    </div>
                </div>
                <div class="attributes_groups_price">
                </div>
            </td>

            <td class="pointer text-right quantity">
                {if $product['quantity_count'] > 1}
                    <div class="btn-group deploy_quantity">
                        <div class="quantity_button btn btn-default" title="{l s='Deploy' mod='masschange'}">
                            {$product['quantity']|escape:'html':'UTF-8'}
                        </div>
                    </div>
                    <div class="attributes_groups_quantity">
                    </div>
                {else}
                    <input type="text" pattern="^[\-0-9]+$" class="base_quantity" name="base_quantity[{$product['id_product']|escape:'html':'UTF-8'}]" value="{$product['quantity']|escape:'html':'UTF-8'}">
                {/if}
            </td>

            <td class="pointer fixed-width-sm text-center">
                <div class="list-action-enable action-enabled {if $product['active']==0} hidden {/if}"
                     title="{l s='Active' mod='masschange'}">
                    <i class="icon-check"></i>
                </div>
                <div class="list-action-enable action-disabled {if $product['active']==1} hidden {/if}"
                     title="{l s='No active' mod='masschange'}">
                    <i class="icon-remove "></i>
                </div>
            </td>

            <td class="fixed-width-sm text-right">
                <div class="btn-group-action">
                    <div class="btn-group pull-right">
                        <a href="{$current_url_product|escape:'htmlall':'UTF-8'}&amp;id_product={$product['id_product']|escape:'html':'UTF-8'}" title="{l s='Preview' mod='masschange'}" target="_blank" class="edit btn btn-default">
                            <i class="icon-edit"></i> {l s='Edit' mod='masschange'}
                        </a>
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                            <i class="icon-caret-down"></i>&nbsp;
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="{$product['href']|escape:'htmlall':'UTF-8'}" title="{l s='Preview' mod='masschange'}" target="_blank">
                                    <i class="icon-eye"></i> {l s='Preview' mod='masschange'}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </td>

        </tr>
    {/foreach}
    </tbody>
{else}
    <tbody>
    <tr>
        <td class="list-empty" colspan="12">
            <div class="list-empty-msg">
                <i class="icon-warning-sign list-empty-icon"></i>
                {l s='No products to display' mod='masschange'}
            </div>
        </td>
    </tr>
    </tbody>
{/if}

</table>


<!-- SELECTED PRODUCTS -->

<div class="row">
    <div class="col-lg-6">
        <div class="btn-group bulk-actions">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                {l s='Bulk actions' mod='masschange'}  <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="#"
                       onclick="javascript:checkDelBoxes($(this).closest('form').get(0), 'selected_id_product[]', true);return false;">
                        <i class="icon-check-sign"></i> &nbsp;{l s='Select all' mod='masschange'}
                    </a>
                </li>
                <li>
                    <a href="#"
                       onclick="javascript:checkDelBoxes($(this).closest('form').get(0), 'selected_id_product[]', false);return false;">
                        <i class="icon-check-empty"></i> &nbsp; {l s='Unselect all' mod='masschange'}
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>

<div class="clearfix">&nbsp;</div>
</div>
</form>

<hr>
<div class="row" id="toolbar-footer">
    <button type="submit" form="form-product" class="btn btn-default pull-right">
        <i class="process-icon-save"></i> <span>{l s='Save' mod='masschange'}</span>
    </button>
</div>


</div>
{addJsDefL name=no_tax_text}{l s='No Tax' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=tax_text}{l s='Tax:' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=for_text}{l s='For: ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=currency_text}{l s='currency ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=country_text}{l s='contry ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=group_text}{l s='group ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=from_quantity_text}{l s='From quantity ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=from_text}{l s='From ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=to_text}{l s='To ' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=general_discount_text}{l s='General discount' mod='masschange' js=1}{/addJsDefL}

{addJsDefL name=warning_selected_products_zero}{l s='Please select products' mod='masschange' js=1}{/addJsDefL}
{addJsDefL name=quest_are_you_sure}{l s='Changes will be relevant immediately. Are you sure?' mod='masschange' js=1}{/addJsDefL}

{addJsDefL name=delete_text}{l s='Delete ' mod='masschange' js=1}{/addJsDefL}