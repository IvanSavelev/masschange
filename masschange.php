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

include_once(_PS_MODULE_DIR_ . 'masschange/masschangemodel.php');

class MassChange extends Module
{
    public function __construct()
    {
        $this->name = 'masschange';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Ivan Savelev';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Simple mass change of products');
        $this->description =
            $this->l('Module allows quickly make changes the parameters of goods, their number, prices, options, etc.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->module_key = '68d5767601694ed9d5c2373eb48c1b89';
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $this->context->controller->addCSS($this->_path . 'views/css/style_masschange.css', 'all');

        $this->context->controller->addJs($this->_path . 'views/js/entry.js', 'all');
        $tax_rules_groups = TaxRulesGroup::getTaxRulesGroups(true);
        $tax_exclude_tax_option = Tax::excludeTaxeOption();
        Media::addJsDef(array('tax_rules_groups' => $tax_rules_groups));
        Media::addJsDef(array('tax_exclude_tax_option' => $tax_exclude_tax_option));
        Media::addJsDef(array('baseDir' => _PS_BASE_URL_ . '/modules/'. (string)$this->name . '/ajax.php'));
        Media::addJsDef(array('id_lang' => $this->context->language->id));
        Media::addJsDef(array('static_token' => Tools::getAdminTokenLite('AdminModules')));
        Media::addJsDef(array('id_employee' => $this->context->employee->id));


        if (Tools::getIsset('save_all') && Tools::getValue('save_all') == 1) { //SAVE

            $message = $this->validate(
                Tools::getValue('base_price'),
                Tools::getValue('base_quantity'),
                Tools::getValue('id_product_attribute_quantity'),
                Tools::getValue('id_product_attribute_price'),
                Tools::getValue('id_specific_price')
            );
            if ($message == '') {
                if ($this->setSave(
                    Tools::getValue('base_price'),
                    Tools::getValue('base_quantity'),
                    Tools::getValue('id_product_attribute_quantity'),
                    Tools::getValue('id_product_attribute_price'),
                    Tools::getValue('id_tax_rules_group'),
                    Tools::getValue('id_specific_price'),
                    Tools::getValue('id_specific_price_delete'),
                    Tools::getValue('id_product_action_enable')
                )) {
                    $message .= $this->displayConfirmation($this->l('Changes successfully saved'));
                } else {
                    $message .= $this->displayError($this->l('There was an error saving'));
                }
            }
        }



        $filter_name = Tools::getValue('filter_name');
        $filter_order = Tools::getValue('filter_order');
        $filter_data = null;
        if ($filter_name && $filter_order) {
        } else {
            $filter_name = 'Id';
            $filter_order = 'asc';
        }
        $filter_data = array('filter_name' => $filter_name, 'filter_order' => $filter_order);

        $find_data = null;
        if (Tools::getIsset('find_data') && !Tools::getIsset('submitResetProductMassChange')) {
            $find_data = Tools::getValue('find_data');
            foreach ($find_data as $key => $find_data_value) {
                if ($find_data_value == "") {
                    unset($find_data[$key]);
                }
            }
        }

        if (Tools::getIsset('selected_id_products_ar')) {
            $selected_id_product = Tools::getValue('selected_id_products_ar');
            $selected_id_product = json_decode($selected_id_product);
            if (count($selected_id_product) != 0) {
                if (Tools::getIsset('massive_price_button')) {
                    if (Validate::isUnsignedFloat(Tools::getValue('massive_price_factor'))) {
                        $this->massiveModifierPrice(
                            Tools::getValue('massive_price_factor'),
                            Tools::getValue('massive_price_factor_prefix'),
                            $selected_id_product
                        );
                        $message = $this->displayConfirmation($this->l('Prices changed successfully'));
                    } else {
                        $message = $this->displayError(
                            $this->l('Please set the ratio correctly, to change the price, for example 1.1')
                        );
                    }
                }

                if (Tools::getIsset('massive_quantity_button')) {
                    if (Validate::isUnsignedFloat(Tools::getValue('massive_quantity_factor'))) {
                        $this->massiveModifierQuantity(
                            Tools::getValue('massive_quantity_factor'),
                            Tools::getValue('massive_quantity_factor_prefix'),
                            $selected_id_product
                        );
                        $message = $this->displayConfirmation($this->l('Quantity of products successfully changed'));
                    } else {
                        $message = $this->displayError(
                            $this->l('Correctly set the factor to change the number of products, for example 1.1')
                        );
                    }
                }

                if (Tools::getIsset('massive_active_on_button')) {
                    $this->massiveModiferActive(1, $selected_id_product);
                    $message = $this->displayConfirmation($this->l('All selected products activate'));
                }

                if (Tools::getIsset('massive_active_off_button')) {
                    $this->massiveModiferActive(0, $selected_id_product);
                    $message = $this->displayConfirmation($this->l('All selected products deactivate'));
                }

                if (Tools::getIsset('massive_tax_button')) {
                    $this->massiveModiferTax(
                        (int)Tools::getValue('massive_id_tax_rules_group'),
                        $selected_id_product
                    );
                    $message = $this->displayConfirmation($this->l('All selected products update tax'));
                }

                if (Tools::getIsset('delete_specific_price_button_massive')) {
                    $this->massiveModiferDelSpecificPrice($selected_id_product);
                    $message = $this->displayConfirmation($this->l('All selected products delete specific price'));
                }
            } else {
                $message = $this->displayWarning($this->l('Please select products'));
            }
        }

        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $limit_description_short = (int)Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') <= 0 ?
                800 : (int)Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');
            $limit_description = 6000;
        } else {
            $limit_description_short = Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') ? Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') : 400;
            $limit_description = 100500;
        }


        $products = $this->getProductsData($filter_data, $find_data, (int)Tools::getValue('id_category'));
        if (count($products) > 1000 && !Tools::getIsset('open_category')) {
            $products = null;
        }


        $token_products = Tools::getAdminToken(
            'AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)Context::getContext()->employee->id
        );

        $current_url_product = 'index.php?controller=AdminProducts&updateproduct&token=' . $token_products;

        if (!isset($message)) {
            $message = null;
        }


        $this->context->smarty->assign(array(
            'products'            => $products,
            'message'             => $message,
            'categoriesTree'      => Category::getRootCategory()->recurseLiteCategTree(0),
            'tax_rules_groups'      => $tax_rules_groups,
            'tax_exclude_tax_option'      => $tax_exclude_tax_option,
            'modules_dir'         => $this->local_path . 'views/templates/admin/category-tree-branch.tpl',
            'find_data'           => $find_data,
            'filter_name'         => mb_strtolower($filter_name),
            'filter_order'        => mb_strtolower($filter_order),
            'id_category'             => Tools::getValue('id_category'),
            'current_url_product'         => $current_url_product,
            'current_url'        => $this->context->link->getAdminLink('AdminModules') .
                '&configure=masschange&tab_module=front_office_features&
                                    module_name=masschange',
        ));


        return $this->display(__FILE__, 'views/templates/admin/entry.tpl');
    }


    private function getProductsData($filter_data, $find_data, $id_category = null)
    {

        $products = MassChangeModel::getProductsData(
            $this->context->language->id,
            $filter_data,
            $find_data,
            $id_category
        );

        $array_quantity_count = MassChangeModel::getCountQuantity();
        $quantity = array();
        foreach ($array_quantity_count as $key => $value) {
            $quantity[$value['id_product']] = $value['count'];
        }

        $id_shop = $this->context->shop->id;
        $currency = $this->context->currency;
        $currency_sign = $this->context->currency->sign;
        $currency_format = $this->context->currency->format;
        $id_lang = $this->context->language->id;

        foreach ($products as $key => $product) {
            $id_product = $products[$key]['id_product'];

            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
                $image_type = ImageType::getFormattedName('cart');
            } else {
                $image_type = ImageType::getFormatedName('cart');
            }

            //IMAGE
            $products[$key]['image_dir'] = _THEME_PROD_DIR_ .
                '/' . chunk_split($product['id_image'], 1, '/') . $product['id_image'] . '-' . $image_type . '.jpg';

            //PRICE AND PREFIX
            $products[$key]['price'] = self::displayPriceNoSign(
                Tools::convertPrice($product['price'], null, true),
                $currency
            );

            $array_format_currency = self::getCurrencyFormat($currency_format, $currency_sign);
            $products[$key]['price_prefix_right'] = $array_format_currency['price_prefix_right'];
            $products[$key]['price_prefix_left'] = $array_format_currency['price_prefix_left'];

            $products[$key]['final_price'] = self::displayPriceNoSign(
                Product::getPriceStatic((int)$product['id_product']),
                $currency
            );

            //QUANTITY COUNT
            $products[$key]['quantity_count'] = $quantity[$id_product];

            //SPECIFIC PRICE
            $products[$key]['specific_price'] = self::getSpecificPrice(
                $id_product,
                $id_shop,
                $id_lang,
                $array_format_currency,
                $currency
            );

            //LINK PRODUCT STORE

            $products[$key]['href'] = $this->getPreviewUrl(new Product($id_product));
        }

        return $products;
    }


    public function getPreviewUrl(Product $product)
    {
        $id_lang = $this->context->language->id;

        if (!ShopUrl::getMainShopDomain()) {
            return false;
        }

        $name_product_url = $product->link_rewrite[$id_lang];
        $id_category = $product->id_category_default;


        $is_rewrite_active = (bool)Configuration::get('PS_REWRITING_SETTINGS');
        $preview_url = $this->context->link->getProductLink(
            $product,
            $name_product_url,
            Category::getLinkRewrite($id_category, $this->context->language->id),
            null,
            $id_lang,
            (int)Context::getContext()->shop->id,
            0,
            $is_rewrite_active
        );

        if (!$product->active) {
            $admin_dir = dirname($_SERVER['PHP_SELF']);
            $admin_dir = Tools::substr($admin_dir, strrpos($admin_dir, '/') + 1);
            $token_products = Tools::getAdminToken(
                'AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)Context::getContext()->employee->id
            );
            $preview_url .= ((strpos($preview_url, '?') === false) ? '?' : '&').
                'adtoken='. $token_products .'&ad='.$admin_dir.'&id_employee='.(int)$this->context->employee->id;
        }

        return $preview_url;
    }


    public static function getSpecificPrice(
        $id_product,
        $id_shop,
        $id_lang,
        $array_format_currency,
        $currency,
        $attribute = 0
    ) {

        $specific_prices = MassChangeModel::getSpecificPrice($id_product, $id_shop, $id_lang, $attribute);

        foreach ($specific_prices as $key_sp => $specific_price) {
            $specific_price = self::getModSpecificPrice($specific_price, $array_format_currency);
            if ($specific_price['reduction_type'] == 'amount') {
                $specific_price['reduction'] = self::displayPriceNoSign($specific_price['reduction'], $currency);
            }

            $specific_prices[$key_sp] = $specific_price;
        }

        return $specific_prices;
    }


    public static function getModSpecificPrice($specific_price, $array_format_currency)
    {
        if ($specific_price['iso_code'] != null) {
            if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
                $cldr = Tools::getCldr(Context::getContext());
                $currency = $cldr->getCurrency($specific_price['iso_code']);
                $format_pattern = $cldr->getCurrencyFormatPattern();
                $currency_specific_price = self::getCurrencyFormat($format_pattern, $currency['symbol']);
            } else {
                $currency_specific_price = self::getCurrencyFormat($specific_price['format'], $specific_price['sign']);
            }

            $specific_price['price_prefix_right'] = $currency_specific_price['price_prefix_right'];
            $specific_price['price_prefix_left'] = $currency_specific_price['price_prefix_left'];
        } else {
            $specific_price['price_prefix_right'] = $array_format_currency['price_prefix_right'];
            $specific_price['price_prefix_left'] = $array_format_currency['price_prefix_left'];
        }
        //Date
        if ($specific_price['from'] > 1) {
            if (Tools::substr($specific_price['from'], 11) == 0) {
                $specific_price['from'] = Tools::substr($specific_price['from'], 0, 10);
            }
        }
        if ($specific_price['to'] > 1) {
            if (Tools::substr($specific_price['to'], 11) == 0) {
                $specific_price['to'] = Tools::substr($specific_price['from'], 0, 10);
            }
        }
        return $specific_price;
    }


    private static function displayPriceNoSign($price, $currency)
    {
        $c_decimals = (int)$currency->decimals * _PS_PRICE_DISPLAY_PRECISION_;
        return number_format($price, $c_decimals, '.', '');
    }


    public static function getCurrencyFormat($currency_format, $currency_sign)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $position = strripos($currency_format, '¤', -1);
            if ($position == 0 || $position == false) {
                $price_prefix_left  = $currency_sign;
                $price_prefix_right = "";
            } else {
                $price_prefix_left  = "";
                $price_prefix_right = $currency_sign;
            }
        } else {
            if ($currency_format == 1 || $currency_format == 3 || $currency_format == 5) {
                $price_prefix_left  = $currency_sign;
                $price_prefix_right = "";
            } else {
                $price_prefix_left  = "";
                $price_prefix_right = $currency_sign;
            }
        }

        return array('price_prefix_left' => $price_prefix_left, 'price_prefix_right' => $price_prefix_right);
    }


    private function massiveModifierQuantity($massive_quantity_factor, $massive_factor_prefix, $selected_id_product)
    {
        $id_shop = $this->context->shop->id;
        $count_quantity_array = MassChangeModel::getCountQuantityNormalize();
        foreach ($selected_id_product as $id_product) {
            if ($count_quantity_array[$id_product] > 1) {
                $count_attribute = $count_quantity_array[$id_product] - 1;
                MassChangeModel::setMassiveQuantityAttr(
                    $massive_quantity_factor,
                    $massive_factor_prefix,
                    $id_product,
                    $count_attribute
                );
                $this->updateSumQuantity($id_product, $id_shop);
            } else {
                MassChangeModel::setMassiveQuantity(
                    $massive_quantity_factor,
                    $massive_factor_prefix,
                    $id_product
                ); //No attribute product
            }
        }
    }


    private function massiveModifierPrice($massive_price_factor, $massive_factor_prefix, $selected_id_product)
    {
        $id_shop = $this->context->shop->id;
        foreach ($selected_id_product as $id_product) {
            MassChangeModel::setMassivePrice($massive_price_factor, $massive_factor_prefix, $id_product, $id_shop);
        }
    }

    private function massiveModiferActive($active, $selected_id_product)
    {
        $id_shop = $this->context->shop->id;
        foreach ($selected_id_product as $id_product) {
            MassChangeModel::setProductAction($active, $id_product, $id_shop);
        }
    }

    private function massiveModiferTax($id_tax_rules_group, $selected_id_product)
    {
        $id_shop = $this->context->shop->id;
        foreach ($selected_id_product as $id_product) {
            MassChangeModel::setTaxGroup($id_tax_rules_group, $id_product, $id_shop);
        }
    }

    private function massiveModiferDelSpecificPrice($selected_id_product)
    {
        $id_shop = $this->context->shop->id;
        foreach ($selected_id_product as $id_product) {
            MassChangeModel::deleteSpecificPriceFromIdProduct($id_product, $id_shop);
        }
    }

    private function setSave(
        $base_price,
        $base_quantity,
        $id_product_attribute_quantity,
        $id_product_attribute_price,
        $id_tax_rules_group,
        $id_specific_price,
        $id_specific_price_delete,
        $id_product_action_enable
    ) {
        $id_shop = $this->context->shop->id;
        //PRICE BEGIN
        if ($base_price) {
            foreach ($base_price as $id_product => $price) {
                if (!MassChangeModel::setBasePrice($price, $id_product, $id_shop)) {
                    return false;
                }
            }
        }

        if ($id_product_attribute_price) {
            foreach ($id_product_attribute_price as $id_product_attribute => $price) {
                if (!MassChangeModel::setAttributePrice($price, $id_product_attribute, $id_shop)) {
                    return false;
                }
            }
        }

        if ($id_tax_rules_group) {
            foreach ($id_tax_rules_group as $id_product => $value_id_tax) {
                if (!MassChangeModel::setTaxGroup($value_id_tax, $id_product, $id_shop)) {
                    return false;
                }
            }
        }

        if ($id_specific_price) {
            foreach ($id_specific_price as $id_product => $price_or_percent) {
                if (!MassChangeModel::setSpecificPrice($price_or_percent, $id_product, $id_shop)) {
                    return false;
                }
            }
        }

        if ($id_specific_price_delete) {
            foreach ($id_specific_price_delete as $id_specific_price => $value) {
                if ($value) {
                    if (!MassChangeModel::deleteSpecificPrice($id_specific_price, $id_shop)) {
                        return false;
                    }
                }
            }
        }
        //PRICE END

        //QUANTITY BEGIN
        if ($base_quantity) {
            foreach ($base_quantity as $id_product => $quantity) {
                if (!MassChangeModel::setBaseQuantity($quantity, $id_product)) {
                    return false;
                }
            }
        }

        if ($id_product_attribute_quantity) {
            $id_product_array = array();
            foreach ($id_product_attribute_quantity as $id_product_attribute => $quantity) {
                //UPDATE stock available
                if (!MassChangeModel::setAttributeQuantity($quantity, $id_product_attribute, $id_shop)) {
                    return false;
                }
                $id_product = MassChangeModel::getIdProductFromAttribute($id_product_attribute);
                $id_product_array[] = $id_product;
            }
            $id_product_array = array_unique($id_product_array);
            //UPDATE SUM
            foreach ($id_product_array as $id_product) {
                $this->updateSumQuantity($id_product, $id_shop);
            }
        }
        //QUANTITY END

        //ACTION ENABLE
        if ($id_product_action_enable) {
            foreach ($id_product_action_enable as $id_product => $active) {
                if (!MassChangeModel::setProductAction($active, $id_product, $id_shop)) {
                    return false;
                }
            }
        }
        return true;
    }

    private function updateSumQuantity($id_product, $id_shop)
    {
        $total_quantity = MassChangeModel::getTotalQuantityFromProductId($id_product, $id_shop);
        StockAvailable::setQuantity((int)$id_product, 0, $total_quantity, $id_shop); //Third-party module
    }

    private function validate(
        $base_price,
        $base_quantity,
        $id_product_attribute_quantity,
        $id_product_attribute_price,
        $id_specific_price
    ) {
        $message = '';
        //PRICE BEGIN
        if ($base_price) {
            foreach ($base_price as $id_product => $price) {
                if (!Validate::isPrice($price)) {
                    $message .= $this->displayError(
                        sprintf($this->l('Enter the base price correctly (ID = %d)'), $id_product)
                    );
                }
            }
        }

        if ($id_product_attribute_price) {
            foreach ($id_product_attribute_price as $id_product_attribute => $price) {
                if (!Validate::isPrice($price)) {
                    $id_product = MassChangeModel::getIdProductFromAttribute($id_product_attribute);
                    $message .= $this->displayError(
                        sprintf($this->l('Enter the attribute price correctly (ID = %d)'), $id_product)
                    );
                }
            }
        }

        if ($id_specific_price) {
            foreach ($id_specific_price as $id_product => $price_or_percent) {
                if (!Validate::isPrice($price_or_percent)) {
                    $message .= $this->displayError(
                        sprintf($this->l('Enter the specific price correctly (ID = %d)'), $id_product)
                    );
                }
            }
        }
        //PRICE END

        //QUANTITY BEGIN
        if ($base_quantity) {
            foreach ($base_quantity as $id_product => $quantity) {
                if (!Validate::isInt($quantity)) {
                    $message .= $this->displayError(
                        sprintf($this->l('The base product quantity is not entered correctly (ID = %d)'), $id_product)
                    );
                }
            }
        }

        if ($id_product_attribute_quantity) {
            foreach ($id_product_attribute_quantity as $id_product_attribute => $quantity) {
                if (!Validate::isInt($quantity)) {
                    $id_product = MassChangeModel::getIdProductFromAttribute($id_product_attribute);
                    $message .= $this->displayError(
                        sprintf($this->l('The base product quantity is not entered correctly (ID = %d)'), $id_product)
                    );
                }
            }
        }
        //QUANTITY END

        return $message;
    }
}
