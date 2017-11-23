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

class MassChangeModel extends ObjectModel
{
    public static function getProductsData($id_lang, $filter_data, $find_data, $id_category = null)
    {

        $sql = "
          SELECT
          `" . _DB_PREFIX_ . "product`.`id_product`,
          `id_image`,
          `" . _DB_PREFIX_ . "product_lang`.`name`,
          `" . _DB_PREFIX_ . "product`.`reference`,
          `" . _DB_PREFIX_ . "category_lang`.`name` AS name_category,
          `" . _DB_PREFIX_ . "product`.`price`,
          `" . _DB_PREFIX_ . "product`.`active`,
          `" . _DB_PREFIX_ . "stock_available`.`quantity`

          ";

        $sql .= " FROM (
          " . _DB_PREFIX_ . "product,
          " . _DB_PREFIX_ . "product_lang,
          " . _DB_PREFIX_ . "category_lang,
          " . _DB_PREFIX_ . "stock_available";
        if ($id_category != null) {
            $sql .= ",  " . _DB_PREFIX_ . "category_product";
        }

        $sql .= ") LEFT JOIN " . _DB_PREFIX_ . "image ON " .
            _DB_PREFIX_ . "image.id_product = " . _DB_PREFIX_ . "product.id_product
            ";

        $sql .= " WHERE " .
            _DB_PREFIX_ . "stock_available.id_product = " . _DB_PREFIX_ . "product.id_product AND " .
            _DB_PREFIX_ . "stock_available.id_product_attribute = 0 AND " .
            _DB_PREFIX_ . "category_lang.id_category = " . _DB_PREFIX_ . "product.id_category_default AND " .
            _DB_PREFIX_ . "product_lang.id_lang = " . (int)$id_lang . " AND " .
            _DB_PREFIX_ . "category_lang.id_lang = " . (int)$id_lang . " AND " .
            "(cover = 1 OR id_image IS NULL) AND ";
        if ($id_category != null) {
            $sql .=
                _DB_PREFIX_ . "product.id_product = " . _DB_PREFIX_ . "category_product.id_product AND " .
                _DB_PREFIX_ . "product_lang.id_product = " . _DB_PREFIX_ . "category_product.id_product AND " .
                _DB_PREFIX_ . "category_product.id_category = " . (int)$id_category;
        } else {
            $sql .=
                _DB_PREFIX_ . "product.id_product = " . _DB_PREFIX_ . "product_lang.id_product";
        }


        if ($find_data != null) {
            $find_sql = '';
            foreach ($find_data as $find_name => $find_value) {
                if ($find_value == '') {
                    continue;
                }
                switch (mb_strtolower($find_name)) {
                    case 'id':
                        $find_sql =  _DB_PREFIX_ . "product.id_product = " . (int)$find_value;
                        break;
                    case 'name':
                        $find_sql = 'LOCATE("'. pSQL($find_value) . '", ' . _DB_PREFIX_ . 'product_lang.name)';
                        break;
                    case 'reference':
                        $find_sql = 'LOCATE("'. pSQL($find_value) . '", ' . _DB_PREFIX_ . 'product.reference)';
                        break;
                    case 'category':
                        $find_sql =  'LOCATE("'. pSQL($find_value) . '", ' . _DB_PREFIX_ . 'category_lang.name)';
                        break;
                    case 'base_price':
                        $find_sql =   _DB_PREFIX_ . "product.price = " . pSQL($find_value);
                        break;
                    case 'quantity':
                        $find_sql = _DB_PREFIX_ . "stock_available.quantity = " . (int)$find_value;
                        break;
                    case 'active':
                        $find_sql = _DB_PREFIX_ . "product.active = " . (int)$find_value;
                        break;
                }
                $sql .= ' AND ' . $find_sql;
            }
        }


        if ($filter_data != null) {
            $filter_name = '';
            switch (mb_strtolower($filter_data['filter_name'])) {
                case 'id':
                    $filter_name =  _DB_PREFIX_ . "product.id_product";
                    break;
                case 'name':
                    $filter_name =  _DB_PREFIX_ . "product_lang.name";
                    break;
                case 'reference':
                    $filter_name =  _DB_PREFIX_ . "product.reference";
                    break;
                case 'category':
                    $filter_name =  "name_category";
                    break;
                case 'base_price':
                    $filter_name =  _DB_PREFIX_ . "product.price";
                    break;
                case 'quantity':
                    $filter_name =  _DB_PREFIX_ . "stock_available.quantity";
                    break;
            }
            $sql .= " ORDER BY " . $filter_name . ' ' . $filter_data['filter_order'];
        }
        $products = Db::getInstance()->executeS($sql);
        return $products;
    }

    public static function getCountQuantity()
    {
        $query = new DbQuery();
        $query->select('COUNT(*) as count');
        $query->select('id_product');
        $query->from('stock_available');
        $query->groupBy('id_product');
        return Db::getInstance()->executeS($query);
    }

    public static function getCountPrice($id_shop)
    {
        $query = new DbQuery();
        $query->select('COUNT(*) as count');
        $query->select('id_product');
        $query->from('product_attribute_shop');
        $query->where('id_shop = ' . (int)$id_shop);
        $query->groupBy('id_product');
        return Db::getInstance()->executeS($query);
    }


    public static function getSpecificPrice($id_product, $id_shop, $id_lang, $attribute = 0)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $sql = "SELECT
                    pc.iso_code,
                    psp.*,
                    pcl.name AS country_name,
                    pgl.name AS group_name " .
                   "FROM " . _DB_PREFIX_ . "specific_price psp" . " " .
                   "LEFT JOIN " . _DB_PREFIX_ . "currency pc  ON pc.id_currency = psp.id_currency" . " " .
                   "LEFT JOIN " . _DB_PREFIX_ . "country_lang pcl  ON " .
                   "pcl.id_country = psp.id_country AND pcl.id_lang = " . (int)$id_lang . " " .
                   "LEFT JOIN " . _DB_PREFIX_ . "group_lang pgl  ON pgl.id_group = psp.id_group AND pgl.id_lang = " .
                   (int)$id_lang . " " .
                   "WHERE id_product = " . (int)$id_product . " " .
                   "AND (id_shop = " . (int)$id_shop . " OR id_shop=0) ";
            if ($attribute) {
                $sql .=  "AND id_product_attribute <> 0";
            } else {
                $sql .=  "AND id_product_attribute = 0";
            }
        } else {
            $sql = "SELECT pc.iso_code, pc.sign, pc.format, psp.*, pcl.name AS country_name, pgl.name AS group_name " .
               "FROM " . _DB_PREFIX_ . "specific_price psp" . " " .
               "LEFT JOIN " . _DB_PREFIX_ . "currency pc  ON pc.id_currency = psp.id_currency" . " " .
               "LEFT JOIN " . _DB_PREFIX_ . "country_lang pcl  ON pcl.id_country = psp.id_country AND pcl.id_lang = " .
               (int)$id_lang . " " .
               "LEFT JOIN " . _DB_PREFIX_ . "group_lang pgl  ON pgl.id_group = psp.id_group AND pgl.id_lang = " .
               (int)$id_lang . " " .
               "WHERE id_product = " . (int)$id_product . " " .
               "AND (id_shop = " . (int)$id_shop . " OR id_shop=0) ";
            if ($attribute) {
                $sql .=  "AND id_product_attribute <> 0";
            } else {
                $sql .=  "AND id_product_attribute = 0";
            }
        }
        $specific_prices = Db::getInstance()->executeS($sql);
        return $specific_prices;
    }


    //PRICE BEGIN

    public static function setBasePrice($price, $id_product, $id_shop)
    {
        self::normalizeValue($price);

        $sql = "UPDATE " .
            _DB_PREFIX_ . "product p, " .
            _DB_PREFIX_ . "product_shop ps
                SET p.price = " . (double)$price . ", " .
            "ps.price = " . (double)$price . " " .
            " WHERE p.id_product = " . (int)$id_product . " AND " .
            "ps.id_product = " . (int)$id_product. " AND " .
            "ps.id_shop = " . (int)$id_shop;
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public static function setAttributePrice($price, $id_product_attribute, $id_shop)
    {
        self::normalizeValue($price);
        $check = true;
        $sql = "UPDATE " .
            _DB_PREFIX_ . "product_attribute_shop
                    SET price = " . (double)$price . ", " .
            "id_shop = " . (int)$id_shop .
            " WHERE " . _DB_PREFIX_ . "product_attribute_shop.id_product_attribute = " . (int)$id_product_attribute;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }
        $sql = "UPDATE " .
            _DB_PREFIX_ . "product_attribute
                    SET price = " . (double)$price . " " .
            " WHERE " . _DB_PREFIX_ . "product_attribute.id_product_attribute = " . (int)$id_product_attribute;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }
        return $check;
    }

    public static function setTaxGroup($id_tax_rules_group, $id_product, $id_shop)
    {
        $check = true;
        $sql = "UPDATE " .
            _DB_PREFIX_ . "product_shop
                    SET id_tax_rules_group = " . (int)$id_tax_rules_group . " " .
            " WHERE id_product = " . (int)$id_product . " AND id_shop = " . (int)$id_shop;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }
        $sql = "UPDATE " .
            _DB_PREFIX_ . "product
                    SET id_tax_rules_group = " . (int)$id_tax_rules_group . " " .
            " WHERE id_product = " . (int)$id_product;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }
        return $check;
    }

    public static function setSpecificPrice($price_or_percent, $id_specific_price, $id_shop)
    {
        self::normalizeValue($price_or_percent);
        $sql = "UPDATE " .
            _DB_PREFIX_ . "specific_price
                SET reduction = IF(reduction_type='percentage', " . (double)$price_or_percent/100 . ", " .
            (double)$price_or_percent . "), " .
            "id_shop = " . (int)$id_shop .
            " WHERE id_specific_price = " . (int)$id_specific_price;
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteSpecificPrice($id_specific_price, $id_shop)
    {
        $sql = "DELETE  FROM " . _DB_PREFIX_ . "specific_price " .
            "WHERE id_shop = " . (int)$id_shop . " AND " .
            "id_specific_price = " . (int)$id_specific_price;
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    //PRICE END

    //QUANTITY BEGIN

    public static function setBaseQuantity($quantity, $id_product)
    {
        $sql = "UPDATE " .
            _DB_PREFIX_ . "stock_available
                    SET quantity = " . (int)$quantity . " " .
            "WHERE id_product_attribute = 0 AND id_product = " . (int)$id_product;

        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public static function setAttributeQuantity($quantity, $id_product_attribute, $id_shop)
    {
        $sql = "UPDATE " .
            _DB_PREFIX_ . "stock_available
                    SET quantity = " . (int)$quantity . ", " .
            "id_shop = " . (int)$id_shop .
            " WHERE id_product_attribute = " . (int)$id_product_attribute;
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getIdProductFromAttribute($id_product_attribute)
    {
        $id_product = Db::getInstance()->getValue(
            "SELECT id_product FROM " . _DB_PREFIX_ . "stock_available
                    WHERE id_product_attribute =" . (int)$id_product_attribute
        );
        return $id_product;
    }

    public static function getTotalQuantityFromProductId($id_product, $id_shop)
    {
        $sql =  "SELECT SUM(quantity) as quantity " .
            "FROM " . _DB_PREFIX_ . "stock_available " .
            "WHERE id_product_attribute <> 0 " .
            "AND id_product = " . (int)$id_product . " " .
            "AND id_shop =" . (int)$id_shop;
        return Db::getInstance()->getValue($sql);
    }

    //QUANTITY END


    //ACTION ENABLE

    public static function setProductAction($active, $id_product, $id_shop)
    {
        $check = true;
        $sql = "UPDATE " .
            _DB_PREFIX_ . "product
                    SET active = " . (int)$active .
            " WHERE id_product = " . (int)$id_product;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }

        $sql = "UPDATE " .
            _DB_PREFIX_ . "product_shop
                    SET active = " . (int)$active .
            " WHERE id_product = " . (int)$id_product . " AND id_shop = " . (int)$id_shop;
        if (!Db::getInstance()->execute($sql)) {
            $check = false;
        }
        return $check;
    }

    //ACTION END

    //MASSIVE BASE QUANTITY

    /**
     * @return array [id_product] = quantity
     */
    public static function getCountQuantityNormalize()
    {
        $query = new DbQuery();
        $query->select('COUNT(*) as count');
        $query->select('id_product');
        $query->from('stock_available');
        $query->groupBy('id_product');
        $quantity_count = Db::getInstance()->executeS($query);

        $quantity_count_normalize = array();
        foreach ($quantity_count as $value) {
            $quantity_count_normalize[$value['id_product']] = $value['count'];
        }
        return $quantity_count_normalize;
    }


    public static function setMassiveQuantity($quantity, $base_factor_prefix, $id_product)
    {
        switch ($base_factor_prefix) {
            case '+':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity + " . (int)$quantity . " " .
                    "WHERE id_product_attribute = 0 AND id_product = " . (int)$id_product;
                break;
            case '-':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity - " . (int)$quantity . " " .
                    "WHERE id_product_attribute = 0 AND id_product = " . (int)$id_product;
                break;
            case '*':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity * " . (double)$quantity . " " .
                    "WHERE id_product_attribute = 0 AND id_product = " . (int)$id_product;
                break;
            case '=':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = " . (int)$quantity . " " .
                    "WHERE id_product_attribute = 0 AND id_product = " . (int)$id_product;
                break;
        }
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }


    public static function setMassiveQuantityAttr($quantity, $base_factor_prefix, $id_product, $count_attribute)
    {
        if ($base_factor_prefix != '*') {
            $quantity = $quantity/$count_attribute;
        }

        switch ($base_factor_prefix) {
            case '+':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity + " . (int)$quantity . " " .
                    "WHERE id_product_attribute <> 0 AND id_product = " . (int)$id_product;
                break;
            case '-':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity - " . (int)$quantity . " " .
                    "WHERE id_product_attribute <> 0 AND id_product = " . (int)$id_product;
                break;
            case '*':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = quantity * " . (double)$quantity . " " .
                    "WHERE id_product_attribute <> 0 AND id_product = " . (int)$id_product;
                break;
            case '=':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "stock_available
                    SET quantity = " . (int)$quantity . " " .
                    "WHERE id_product_attribute <> 0 AND id_product = " . (int)$id_product;
                break;
        }
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }


    //PRICE MASSIVE

    /**
     * @param $id_shop
     * @return array [id_product] = quantity
     */
    public static function getCountPriceNormalize($id_shop)
    {
        $query = new DbQuery();
        $query->select('COUNT(*) as count');
        $query->select('id_product');
        $query->from('product_attribute_shop');
        $query->where('id_shop = ' . (int)$id_shop);
        $query->groupBy('id_product');
        $price_count = Db::getInstance()->executeS($query);

        $price_count_normalize = array();
        foreach ($price_count as $value) {
            $price_count_normalize[$value['id_product']] = $value['count'];
        }
        return $price_count_normalize;
    }


    public static function setMassivePrice($price, $base_factor_prefix, $id_product, $id_shop)
    {
        switch ($base_factor_prefix) {
            case '+':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "product p, " .
                    _DB_PREFIX_ . "product_shop ps
                SET p.price = IF(p.price + " . (double)$price . "> 0, p.price + " . (double)$price . ", 0), " .
                    "ps.price = IF(ps.price + " . (double)$price . "> 0, ps.price + " . (double)$price . ", 0) " .
                    " WHERE p.id_product = " . (int)$id_product . " AND " .
                    "ps.id_product = " . (int)$id_product. " AND " .
                    "ps.id_shop = " . (int)$id_shop;
                break;
            case '-':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "product p, " .
                    _DB_PREFIX_ . "product_shop ps
                 SET p.price = IF(p.price - " . (double)$price . "> 0, p.price - " . (double)$price . ", 0), " .
                    "ps.price = IF(ps.price - " . (double)$price . "> 0, ps.price - " . (double)$price . ", 0) " .
                    " WHERE p.id_product = " . (int)$id_product . " AND " .
                    "ps.id_product = " . (int)$id_product. " AND " .
                    "ps.id_shop = " . (int)$id_shop;
                break;
            case '*':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "product p, " .
                    _DB_PREFIX_ . "product_shop ps
                 SET p.price = IF(p.price * " . (double)$price . "> 0, p.price * " . (double)$price . ", 0), " .
                    "ps.price = IF(ps.price * " . (double)$price . "> 0, ps.price * " . (double)$price . ", 0) " .
                    " WHERE p.id_product = " . (int)$id_product . " AND " .
                    "ps.id_product = " . (int)$id_product. " AND " .
                    "ps.id_shop = " . (int)$id_shop;
                break;
            case '=':
                $sql = "UPDATE " .
                    _DB_PREFIX_ . "product p, " .
                    _DB_PREFIX_ . "product_shop ps
                SET p.price = IF(" . (double)$price . "> 0, " . (double)$price . ", 0), " .
                    "ps.price = IF(" . (double)$price . "> 0, " . (double)$price . ", 0) " .
                    " WHERE p.id_product = " . (int)$id_product . " AND " .
                    "ps.id_product = " . (int)$id_product. " AND " .
                    "ps.id_shop = " . (int)$id_shop;
                break;
        }
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }


    public static function deleteSpecificPriceFromIdProduct($id_product, $id_shop)
    {
        $sql = "DELETE  FROM " . _DB_PREFIX_ . "specific_price " .
            "WHERE id_shop = " . (int)$id_shop . " AND " .
            "id_product = " . (int)$id_product;
        if (Db::getInstance()->execute($sql)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Normalize Value
     * @param $value
     */
    public static function normalizeValue(&$value)
    {
        if ($value < 0 || $value == "" || !is_numeric((double)$value)) {
            $value = 0;
        }
    }
}
