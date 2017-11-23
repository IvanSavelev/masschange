<?php
/**
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
 */

// Located in /modules/mymodule/ajax.php
require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__).'/masschange.php');


//Data checking begin

$context = Context::getContext();
$context->employee = new Employee(Tools::getValue('id_employee'), Tools::getValue('id_lang'), 1); //TODO
$verifying_token = Tools::getAdminTokenLite('AdminModules', $context);
$token = Tools::getValue('token');
if (Configuration::get('PS_TOKEN_ENABLE') == 1 && strcmp($verifying_token, $token) != 0) {
    $module = new MassChange();
    echo json_encode(array('error' => $module->l('Invalid token', 'ajax')));
    exit;
}
if (!Tools::getIsset('ajaxPriceOpen') && !Tools::getIsset('ajaxQuantityOpen')) {
    $module = new MassChange();
    echo json_encode(array('error' => $module->l('Invalid ajax', 'ajax')));
    exit;
}
//Data checking end

if (Tools::getIsset('ajaxPriceOpen')) {
    $id_shop = 1; //TODO
    $id_product = Tools::getValue('id_product');
    $id_lang = Tools::getValue('id_lang');

    $query = new DbQuery();
    $query->select('pag.id_attribute_group');
    $query->select('psa.id_product_attribute');
    $query->select('ppa.price');
    $query->select('ppa.default_on AS default_attribute');
    $query->select('pag.name AS name_group');
    $query->select('pal.name AS name_field');

    $query->from('stock_available', 'psa');
    $query->innerJoin('product_attribute_combination', 'pac', 'psa.id_product_attribute = pac.id_product_attribute');
    $query->innerJoin('product_attribute_shop', 'ppa', 'ppa.id_product_attribute = pac.id_product_attribute');
    $query->innerJoin('attribute', 'pa', 'pa.id_attribute = pac.id_attribute');
    $query->innerJoin('attribute_group_lang', 'pag', 'pag.id_attribute_group = pa.id_attribute_group');
    $query->innerJoin('attribute_lang', 'pal', 'pal.id_attribute = pac.id_attribute');

    $query->where('ppa.id_product = ' . (int)$id_product);
    $query->where('ppa.id_shop = ' . (int)$id_shop);
    $query->where('pag.id_lang = ' . (int)$id_lang);
    $query->where('pal.id_lang = ' . (int)$id_lang);

    $query->orderBy('psa.id_product_attribute ASC');
    $query->orderBy('pag.id_attribute_group ASC');

    //$gd = $query->build();
    $stock_available = Db::getInstance()->executeS($query);
    $norm_stock_available = array();
    foreach ($stock_available as $key => $value) {
        if (isset($norm_stock_available[$value['id_product_attribute']])) {
            unset($value['quantity']);
            unset($value['default_attribute']);
            $norm_stock_available[$value['id_product_attribute']][] = $value;
        } else {
            $norm_stock_available[$value['id_product_attribute']] = array();
            $norm_stock_available[$value['id_product_attribute']]['price'] = $value['price'];
            $norm_stock_available[$value['id_product_attribute']]['id_product_attribute'] =
                $value['id_product_attribute'];
            if (isset($value['default_attribute'])) {
                $norm_stock_available[$value['id_product_attribute']]['default_attribute'] = 1;
            }
            unset($value['quantity']);
            unset($value['default_attribute']);
            $norm_stock_available[$value['id_product_attribute']][] = $value;
        }
    }

    $query = new DbQuery();
    $query->select('id_tax_rules_group');
    $query->from('product_shop');
    $query->where('id_product = ' . (int)$id_product);
    $query->where('id_shop = ' . (int)$id_shop);
    $id_tax_rules_group = Db::getInstance()->getValue($query);

    $currency = CurrencyCore::getDefaultCurrency();
    $currency_sign = $currency->sign;
    $currency_format = $currency->format;
    $array_format_currency = MassChange::getCurrencyFormat($currency_format, $currency_sign);
    $specific_price = MassChange::getSpecificPrice(
        $id_product,
        $id_shop,
        $id_lang,
        $array_format_currency,
        $currency,
        1
    );

    echo json_encode(array(
        'combination' => $norm_stock_available,
        'id_tax_rules_group' => $id_tax_rules_group,
        'specific_price' => $specific_price
    ));
    exit;
}


if (Tools::getIsset('ajaxQuantityOpen')) {
    $id_shop = 1; //TODO
    $id_product = Tools::getValue('id_product');
    $id_lang = Tools::getValue('id_lang');

    $query = new DbQuery();
    $query->select('pag.id_attribute_group');
    $query->select('psa.id_product_attribute');
    $query->select('ppas.default_on AS default_attribute');
    $query->select('psa.quantity');
    $query->select('pag.name AS name_group');
    $query->select('pal.name AS name_field');

    $query->from('stock_available', 'psa');
    $query->innerJoin('product_attribute_shop', 'ppas', 'psa.id_product_attribute = ppas.id_product_attribute');
    $query->innerJoin('product_attribute_combination', 'pac', 'psa.id_product_attribute = pac.id_product_attribute');
    $query->innerJoin('attribute', 'pa', 'pa.id_attribute = pac.id_attribute');
    $query->innerJoin('attribute_group_lang', 'pag', 'pag.id_attribute_group = pa.id_attribute_group');
    $query->innerJoin('attribute_lang', 'pal', 'pal.id_attribute = pac.id_attribute');

    $query->where('ppas.id_product = ' . (int)$id_product);
    $query->where('ppas.id_shop = ' . (int)$id_shop);
    $query->where('pag.id_lang = ' . (int)$id_lang);
    $query->where('pal.id_lang = ' . (int)$id_lang);
    $query->where('psa.id_product_attribute <> 0');

    $query->orderBy('psa.id_product_attribute ASC');
    $query->orderBy('pag.id_attribute_group ASC');

    //$gd = $query->build();
    $stock_available = Db::getInstance()->executeS($query);
    $norm_stock_available = array();
    foreach ($stock_available as $key => $value) {
        if (isset($norm_stock_available[$value['id_product_attribute']])) {
            unset($value['quantity']);
            unset($value['default_attribute']);
            $norm_stock_available[$value['id_product_attribute']][] = $value;
        } else {
            $norm_stock_available[$value['id_product_attribute']] = array();
            $norm_stock_available[$value['id_product_attribute']]['quantity'] = $value['quantity'];
            $norm_stock_available[$value['id_product_attribute']]['id_product_attribute'] =
                $value['id_product_attribute'];
            if (isset($value['default_attribute'])) {
                $norm_stock_available[$value['id_product_attribute']]['default_attribute'] = 1;
            }
            unset($value['quantity']);
            unset($value['default_attribute']);
            $norm_stock_available[$value['id_product_attribute']][] = $value;
        }
    }
    echo json_encode(array('quantity' => $norm_stock_available));
    exit;
}
