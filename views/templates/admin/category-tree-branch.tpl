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

<li {if isset($last) && $last == 'true'}class="last"{/if}>
    <a href="{$current_url|escape:'htmlall':'UTF-8'}&amp;id_category={$node.id|escape:'quotes':'UTF-8'}&amp;open_category=1"
       title="{$node.desc|escape:'html':'UTF-8'}"
        {if $id_category == $node.id} id="category_check" {/if}>
        {$node.name|escape:'html':'UTF-8'}</a>
    {if $node.children|@count > 0}
        <ul>
            {foreach from=$node.children item=child name=categoryTreeBranch}
                {if $smarty.foreach.categoryTreeBranch.last}
                    {include file="$modules_dir" node=$child last='true' id_category=$id_category}
                {else}
                    {include file="$modules_dir" node=$child last='false' id_category=$id_category}
                {/if}
            {/foreach}
        </ul>
    {/if}
</li>