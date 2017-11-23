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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2017 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
$(document).ready(function () {

    var default_class = 'default';

    //$('[name ^= base_price]').change(function () {
    //    var context_tr = $(this).parent().parent().parent();
    //    $('[name ^= final_price]', context_tr).val(this.value);
    //});
    //$('[name ^= final_price]').change(function () {
    //    var context_tr = $(this).parent().parent().parent();
    //    $('[name ^= base_price]', context_tr).val(this.value);
    //});



    $(".final_price").delegate(".delete_specific_price", "click", function(){
        var context_tr = $(this).parent().parent(); //specific_price_box
        $("[name ^= id_specific_price_delete]", context_tr).val(1);
        $(context_tr).hide();
    });




    $('.list-action-enable').click(function () {

        var new_active = null;
        if($(this).hasClass('action-enabled')) {
            new_active = 0;
        }
        if($(this).hasClass('action-disabled')) {
            new_active = 1;
        }
        if(new_active == null) {
            alert('error active');
        }

        var context_tr = $(this).parent().parent();
        var id_product = $("[name ^= id_product]", context_tr).val();

        if(new_active) {
            $(".action-enabled", context_tr).removeClass('hidden');
            $(".action-disabled", context_tr).addClass('hidden');
        } else {
            $(".action-enabled", context_tr).addClass('hidden');
            $(".action-disabled", context_tr).removeClass('hidden');
        }

        $(this).after('<input type="hidden" name="id_product_action_enable[' + id_product + ']" value="' + new_active + '" />');
    });


    $('.quantity_button').click(function () {
        var context_tr = $(this).parent().parent().parent();
        var id_product = $("[name ^= id_product]", context_tr).val();
        var quantity_visible = $("[name = quantity_visible]", context_tr).is("[name = quantity_visible]");
        if(quantity_visible) {
            quantity_visible = $(".attributes_groups_quantity", context_tr).is(":visible");
        }

        var data_val = "ajaxQuantityOpen=" +
            '&id_employee=' + id_employee +
            '&id_product=' + id_product +
            '&id_lang=' + id_lang +
            '&token=' + static_token;
        $.ajax({
            url: baseDir,
            data: data_val,
            dataType: 'json',
            cache: 'false',
            success: function (json) {
                if (typeof json['error'] !== "undefined") {  //if invalid token
                    alert(json['error']);
                    return;
                }
                if(quantity_visible) {
                    $(".attributes_groups_quantity", context_tr).hide();
                    $(".quantity_button", context_tr).css({'margin-top' : '0'});
                } else {
                    $(".quantity_button", context_tr).css({'margin-top' : '5px'});
                    $(".attributes_groups_quantity", context_tr).empty();
                    $(".attributes_groups_quantity", context_tr).show();
                    var discount_sub = '<span name="quantity_visible" hidden></span>' +
                        '<div class="box_ajax">';
                    $.each(json['quantity'], function(index, value){
                        var default_quantity_class = '';
                        if(value['default_attribute'] == 1) {
                            default_quantity_class = default_class;
                        }
                        discount_sub += '<div class="quantity_box ' + default_quantity_class + '">';
                        discount_sub += '<div class="table-box">';
                        discount_sub += '<table class="group">';
                        $.each(value, function(index_sub, value_sub){
                            if(index_sub != 'quantity' && index_sub != 'id_product_attribute' && index_sub != 'default_attribute') {
                                discount_sub += '<tr>';
                                discount_sub += '<td class="name_group">';
                                discount_sub += value_sub['name_group'];
                                discount_sub += '</td>';
                                discount_sub += '<td class="name_field">';
                                discount_sub += value_sub['name_field'];
                                discount_sub += '</td>';
                                discount_sub += '</tr>';
                            }
                        });
                        discount_sub += '</table>';
                        discount_sub += '</div>';
                        discount_sub += '<div class="input-box">';
                        discount_sub += '<input type="text" pattern="^[\\-0-9]+$" class="pull-left" name="id_product_attribute_quantity[' + value['id_product_attribute'] + ']" value="' + value['quantity'] + '">';
                        discount_sub += '</div>';
                        discount_sub += '</div>';
                    });
                    discount_sub += '</div>';
                    $(".attributes_groups_quantity", context_tr).append(discount_sub);
                }

            },
            error: function (res) {
                alert('error_ajaxQuantityOpen');
            }
        });
    });


    $('.price_button').click(function () {
        var context_tr = $(this).parent().parent().parent();
        var id_product = $("[name ^= id_product]", context_tr).val();
        var price_visible = $("[name = price_visible]", context_tr).is("[name = price_visible]");
        if(price_visible) {
            price_visible = $(".attributes_groups_price", context_tr).is(":visible");
        }
        var data_val = "ajaxPriceOpen=" +
            '&id_employee=' + id_employee +
            '&id_product=' + id_product +
            '&id_lang=' + id_lang +
            '&token=' + static_token;
        $.ajax({
            url: baseDir,
            data: data_val,
            dataType: 'json',
            cache: 'false',
            success: function (json) {
                if (typeof json['error'] !== "undefined") {  //if invalid token
                    alert(json['error']);
                    return;
                }
                if(price_visible) {
                    $(".attributes_groups_price", context_tr).hide();
                    $(".price_button", context_tr).css({'margin-top' : '0'});
                } else {
                    $(".price_button", context_tr).css({'margin-top' : '5px'});
                    $(".attributes_groups_price", context_tr).empty();
                    $(".attributes_groups_price", context_tr).show();
                    var discount_sub = '<span name="price_visible" hidden></span><div class="box_ajax">';
                    //TAX
                    discount_sub += '<div class="tax">';
                    discount_sub += '<div class="text-left">';
                    discount_sub += tax_text;
                    discount_sub += '</div>';
                    discount_sub += '<select name="id_tax_rules_group[' + id_product + ']" id="id_tax_rules_group" ';
                    if(tax_exclude_tax_option) {
                        discount_sub += 'disabled="disabled"';
                    }
                    discount_sub += '>';
                    discount_sub += '<option value="0">' + no_tax_text + '</option>';
                    $.each(tax_rules_groups, function(index, tax_rules_group){
                        discount_sub += '<option value="' + tax_rules_group.id_tax_rules_group + '" ';
                        if(json['id_tax_rules_group'] == tax_rules_group.id_tax_rules_group) {
                            discount_sub += 'selected="selected"';
                        }
                        discount_sub += '>';
                        discount_sub += tax_rules_group['name'];
                        discount_sub += '</option>';
                    });
                    discount_sub += '</select>';
                    discount_sub += '</div></div><hr>';


                    //COMBINATION
                    $.each(json['combination'], function(index, value){
                        var default_price_class = '';
                        if(value['default_attribute'] == 1) {
                            default_price_class = default_class;
                        }

                        var specific_price_class = '';


                        $.each(json['specific_price'], function(index_sub, value_sub){
                            if(value['id_product_attribute'] == value_sub['id_product_attribute']) {
                                specific_price_class = 'specific_price';
                            }
                        });
                        discount_sub += '<div class="price_box ' + default_price_class + ' ' + specific_price_class + '">';

                        $.each(json['specific_price'], function(index_sub, value_sub){
                            if(value['id_product_attribute'] == value_sub['id_product_attribute']) {
                                discount_sub += htmlViewSpecificPrice(value_sub);
                            }
                        });
                        discount_sub += '<div class="table-box">';
                        discount_sub += '<table class="group">';
                        $.each(value, function(index_sub, value_sub){
                            if(index_sub != 'price' && index_sub != 'id_product_attribute' && index_sub != 'default_attribute') {
                                discount_sub += '<tr>';
                                discount_sub += '<td class="name_group">';
                                discount_sub += value_sub['name_group'];
                                discount_sub += '</td>';
                                discount_sub += '<td class="name_field">';
                                discount_sub += value_sub['name_field'];
                                discount_sub += '</td>';
                                discount_sub += '</tr>';
                            }
                        });
                        discount_sub += '</table>';
                        discount_sub += '</div>';
                        discount_sub += '<div class="input-box">';
                        discount_sub += '<input type="text" pattern="\\d+(\\.\\d*)?" class="pull-left" name="id_product_attribute_price[' + value['id_product_attribute'] + ']" value="' + value['price'] + '">';
                        discount_sub += '</div>';
                        discount_sub += '</div>';
                    });

                    $(".attributes_groups_price", context_tr).append(discount_sub);
                }
            },
            error: function (res) {
                alert('error_ajaxPriceOpen');
            }
        });
    });


    function htmlViewSpecificPrice(value_sub) {

        var discount_sub = '';
        discount_sub += '<div class="specific_price_box specific_price_box_attr pull-right">';
        var check = 0;

        if (value_sub['iso_code'] != null) {
            discount_sub += '<p class="text-left"">';
            discount_sub += '<label>' + for_text + '</label>';
            discount_sub += '<span>' + currency_text + value_sub['iso_code'] + '</span>';
            discount_sub += '</p>';
            check = 1;
        }
        if (value_sub['country_name'] != null) {
            discount_sub += '<p class="text-left">';
            discount_sub += '<label>' + for_text + '</label>';
            discount_sub += '<span>' + country_text + value_sub['country_name'] + '</span>';
            discount_sub += '</p>';
            check = 1;
        }
        if (value_sub['group_name'] != null) {
            discount_sub += '<p class="text-left">';
            discount_sub += '<label>' + for_text + '</label>';
            discount_sub += '<span>' + group_text + value_sub['group_name'] + '</span>';
            discount_sub += '</p>';
            check = 1;
        }
        if (value_sub['from_quantity'] > 1) {
            discount_sub += '<p class="text-left">';
            discount_sub += '<label>' + for_text + '</label>';
            discount_sub += '<span>' + from_quantity_text + value_sub['from_quantity'] + '</span>';
            discount_sub += '</p>';
            check = 1;
        }
        if (value_sub['from'] > 1 || value_sub['to'] > 1) {
            discount_sub += '<p class="text-left">';
            discount_sub += '<label>' + for_text + '</label>';

            if (value_sub['from'] > 1 && value_sub['to'] > 1) {
                discount_sub += '<span>' + from_text + value_sub['from'] + ' - ' + value_sub['to'] + '</span>';
            }
            if (value_sub['from'] > 1 && value_sub['to'] == 0) {
                discount_sub += '<span>' + from_text + value_sub['from'] + '</span>';
            }
            if (value_sub['from'] == 0 && value_sub['to'] > 1) {
                discount_sub += '<span>' + to_text + value_sub['to'] + '</span>';
            }
            discount_sub += '</p>';
            check = 1;
        }
        if (check == 0) {
            discount_sub += '<p class="text-left">';
            discount_sub += '<label>' + general_discount_text + '</label>';
            discount_sub += '</p>';
        }

        discount_sub += '<div class="input-group">';
        discount_sub += '<input type="hidden" name="id_specific_price_delete[' + value_sub['id_specific_price'] + ']" value="0" />'
        if (value_sub['reduction_type'] == 'amount') {
            if(value_sub['price_prefix_left'] != "") {
                discount_sub += '<span class="input-group-addon">' + value_sub['price_prefix_left'] + '</span>';
            }
        }

        discount_sub += '<input type="text" pattern="\\d+(\\.\\d*)?" name="id_specific_price[' + value_sub['id_specific_price'] + ']" ';
        discount_sub += 'value="';
        if(value_sub['reduction_type'] == 'amount') {
            discount_sub += value_sub['reduction'];
        }
        if(value_sub['reduction_type'] == 'percentage') {
            discount_sub += value_sub['reduction']*100;
        }
        discount_sub += '">';

        if(value_sub['reduction_type'] == 'amount') {
            if(value_sub['price_prefix_right'] != "") {
                discount_sub += '<span class="input-group-addon">';
                discount_sub += value_sub['price_prefix_right'];
                discount_sub += '</span>';
            }
        }
        if(value_sub['reduction_type'] == 'percentage') {
            discount_sub += '<span class="input-group-addon">%</span>';
        }

        discount_sub += '<div class=" btn btn-default pull-left delete_specific_price" title="'+ delete_text +'">';
        discount_sub += '<span><i class="icon-trash"></i></span>';
        discount_sub += '</div>';

        discount_sub += '</div>';
        discount_sub += '<br>';
        discount_sub += '</div>';


        return discount_sub;

    }


    $('#massive_form').submit(function() {
        var arraySp =[];
        $("[name ^= selected_id_product]:checked").each(function() {
            arraySp.push($(this).val());
        });
        if(arraySp.length) {
            if(confirm(quest_are_you_sure)) {
                var stringMas_selected_id_product = JSON.stringify(arraySp);
                $(this).append('<input type="hidden" name="selected_id_products_ar" value= ' + stringMas_selected_id_product + ' / >');
                return true;
            } else {
                return false;
            }
        } else {
            alert(warning_selected_products_zero);
            return false;
        }

    });

    $('[name="submitFilterMassChange"]').click(function() {
        $('[name="save_all"]').val('0');
    });
    $('[name="submitResetProductMassChange"]').click(function() {
        $('[name="save_all"]').val('0');
    });


});

