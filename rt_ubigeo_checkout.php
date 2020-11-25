<?php
add_filter('woocommerce_states', 'rt_ubigeo_remove_peru_state');

function rt_ubigeo_remove_peru_state($states)
{
    $states['PE'] = array();
    return $states;
}

add_filter('woocommerce_country_locale_field_selectors', 'rt_ubigeo_country_locale_field_selectors');

function rt_ubigeo_country_locale_field_selectors($locale_fields)
{
    $custom_locale_fields = array(
        'departamento' => '#billing_departamento_field, #shipping_departamento_field',
        'provincia' => '#billing_provincia_field, #shipping_provincia_field',
        'distrito' => '#billing_distrito_field, #shipping_distrito_field',
    );

    $locale_fields = array_merge($locale_fields, $custom_locale_fields);

    return $locale_fields;
}

add_filter('woocommerce_default_address_fields', 'rt_ubigeo_default_address_fields');

function rt_ubigeo_default_address_fields($fields)
{
    $custom_fields = array(
        'departamento' => array(
            'hidden' => true,
            'required' => false,
        ),
        'provincia' => array(
            'hidden' => true,
            'required' => false,
        ),
        'distrito' => array(
            'hidden' => true,
            'required' => false,
        ),
    );

    $fields = array_merge($fields, $custom_fields);

    return $fields;
}

add_filter('woocommerce_get_country_locale', 'rt_ubigeo_get_country_locale');

function rt_ubigeo_get_country_locale($locale)
{
    $locale['PE']['departamento'] = array(
        'required' => true,
        'hidden' => false,
    );

    $locale['PE']['provincia'] = array(
        'required' => true,
        'hidden' => false,
    );

    $locale['PE']['distrito'] = array(
        'required' => true,
        'hidden' => false,
    );

    $locale['PE']['state'] = array(
        'required' => false,
        'hidden' => true,
    );

    $locale['PE']['city'] = array(
        'required' => false,
        'hidden' => true,
    );

    $locale['PE']['postcode'] = array(
        'required' => false,
        'hidden' => true,
    );

    return $locale;
}

add_filter('woocommerce_checkout_fields', 'rt_ubigeo_wc_checkout_fields', 99);

function rt_ubigeo_wc_checkout_fields($fields)
{
    $fields['billing']['billing_phone']['priority'] = 34;
    $fields['billing']['billing_email']['priority'] = 36;
    $fields['billing']['billing_address_1']['priority'] = 74;
    $fields['billing']['billing_address_2']['priority'] = 76;

    $fields['shipping']['shipping_phone']['priority'] = 34;
    $fields['shipping']['shipping_email']['priority'] = 36;
    $fields['shipping']['shipping_address_1']['priority'] = 74;
    $fields['shipping']['shipping_address_2']['priority'] = 76;

    $fields['billing']['billing_departamento'] = [
        'type' => 'select',
        'label' => 'Departamento',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => rt_ubigeo_get_departamentos_for_select(),
        'priority' => 65
    ];

    $fields['billing']['billing_provincia'] = [
        'type' => 'select',
        'label' => 'Provincia',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => [
            '' => 'Seleccionar Provincia',
        ],
        'priority' => 66
    ];

    $fields['billing']['billing_distrito'] = [
        'type' => 'select',
        'label' => 'Distrito',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => [
            '' => 'Seleccionar Distrito',
        ],
        'priority' => 67
    ];

    $fields['shipping']['shipping_departamento'] = [
        'type' => 'select',
        'label' => 'Departamento',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => rt_ubigeo_get_departamentos_for_select(),
        'priority' => 65
    ];

    $fields['shipping']['shipping_provincia'] = [
        'type' => 'select',
        'label' => 'Provincia',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => [
            '' => 'Seleccionar Provincia',
        ],
        'priority' => 66
    ];

    $fields['shipping']['shipping_distrito'] = [
        'type' => 'select',
        'label' => 'Distrito',
        'required' => false,
        'class' => array('form-row-wide'),
        'clear' => true,
        'options' => [
            '' => 'Seleccionar Distrito',
        ],
        'priority' => 67
    ];

    return $fields;
}

add_filter('default_checkout_billing_departamento', 'rt_ubigeo_change_default_checkout_departamento');

function rt_ubigeo_change_default_checkout_departamento()
{
    return '0';
}

add_filter('default_checkout_shipping_departamento', 'rt_ubigeo_change_default_checkout_shipping_departamento');

function rt_ubigeo_change_default_checkout_shipping_departamento()
{
    return '0';
}

add_filter('woocommerce_default_address_fields', 'rt_ubigeo_custom_wc_default_address_fields');

function rt_ubigeo_custom_wc_default_address_fields($address_fields)
{
    $address_fields['phone']['priority'] = 34;
    $address_fields['email']['priority'] = 36;
    $address_fields['address_1']['priority'] = 74;
    $address_fields['address_2']['priority'] = 76;
    return $address_fields;
}

add_action('woocommerce_after_checkout_form', 'rt_ubigeo_custom_jscript_checkout');

function rt_ubigeo_custom_jscript_checkout()
{
    ?>
    <script>
        jQuery(document).ready(function () {

            jQuery("#billing_departamento").select2();
            jQuery("#billing_provincia").select2();
            jQuery("#billing_distrito").select2();
            jQuery("#shipping_departamento").select2();
            jQuery("#shipping_provincia").select2();
            jQuery("#shipping_distrito").select2();

            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>"

            function rt_ubigeo_event_departamento(select, selectType) {
                var data = {
                    'action': 'rt_ubigeo_load_provincias_front',
                    'idDepa': jQuery(select).val()
                }

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    beforeSend: function (xhr, settings) {
                        jQuery('form.woocommerce-checkout').addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                    },
                    success: function (response) {
                        jQuery('#' + selectType + '_provincia').html('<option value="">Seleccionar Provincia</option>')
                        jQuery('#' + selectType + '_distrito').html('<option value="">Seleccionar Distrito</option>')

                        if (response) {
                            for (var r in response) {
                                jQuery('#' + selectType + '_provincia').append('<option value=' + r + '>' + response[r] + '</option>')
                            }
                        }
                    },
                    complete: function (xhr, ts) {
                        jQuery('form.woocommerce-checkout').removeClass('processing').unblock()
                    }
                })
            }

            function rt_ubigeo_event_provincia(select, selectType) {
                var data = {
                    'action': 'rt_ubigeo_load_distritos_front',
                    'idProv': jQuery(select).val()
                }

                jQuery.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: data,
                    dataType: 'json',
                    beforeSend: function (xhr, settings) {
                        jQuery('form.woocommerce-checkout').addClass('processing').block({
                            message: null,
                            overlayCSS: {
                                background: '#fff',
                                opacity: 0.6
                            }
                        });
                    },
                    success: function (response) {
                        jQuery('#' + selectType + '_distrito').html('<option value="">Seleccionar Distrito</option>')

                        if (response) {
                            for (var r in response) {
                                jQuery('#' + selectType + '_distrito').append('<option value=' + r + '>' + response[r] + '</option>')
                            }
                        }
                    },
                    complete: function (xhr, ts) {
                        jQuery('form.woocommerce-checkout').removeClass('processing').unblock()
                    }
                })
            }

            jQuery('#billing_departamento').on('change', function () {
                rt_ubigeo_event_departamento(this, 'billing')
            })
            jQuery('#shipping_departamento').on('change', function () {
                rt_ubigeo_event_departamento(this, 'shipping')
            })

            jQuery('#billing_provincia').on('change', function () {
                rt_ubigeo_event_provincia(this, 'billing')
            })
            jQuery('#shipping_provincia').on('change', function () {
                rt_ubigeo_event_provincia(this, 'shipping')
            })

            jQuery('#billing_distrito, #shipping_distrito').on('change', function () {
                jQuery(document.body).trigger("update_checkout", {update_shipping_method: true})
            })

            jQuery('#billing_country').on('change', function () {
                jQuery('#billing_departamento').val('').trigger('change');
                jQuery('#billing_provincia').val('').trigger('change');
                jQuery('#billing_distrito').val('').trigger('change');
            });
        })
    </script>
    <?php
}

add_action('woocommerce_checkout_update_order_review', 'rt_ubigeo_checkout_update_refresh_shipping_methods', 10, 1);

function rt_ubigeo_checkout_update_refresh_shipping_methods($post_data)
{
    $packages = WC()->cart->get_shipping_packages();
    foreach ($packages as $package_key => $package) {
        WC()->session->set('shipping_for_package_' . $package_key, false); // Or true
    }
}

add_action('woocommerce_after_checkout_validation', 'rt_ubigeo_custom_wc_checkout_fields_validation', 999, 2);

function rt_ubigeo_custom_wc_checkout_fields_validation($fields, $errors)
{
    if ('PE' === $fields['billing_country']) {
        if ('' === $fields['billing_departamento']) {
            $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Billing Departamento') . '</strong>'), 'Billing Departamento'));
        }
        if ('' === $fields['billing_provincia']) {
            $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Billing Provincia') . '</strong>'), 'Billing Provincia'));
        }
        if ('' === $fields['billing_distrito']) {
            $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Billing Distrito') . '</strong>'), 'Billing Distrito'));
        }
    }

    if (1 == $fields['ship_to_different_address']) {
        if ('PE' === $fields['shipping_country']) {
            if ('' === $fields['shipping_departamento']) {
                $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Shipping Departamento') . '</strong>'), 'Shipping Departamento'));
            }
            if ('' === $fields['shipping_provincia']) {
                $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Shipping Provincia') . '</strong>'), 'Shipping Provincia'));
            }
            if ('' === $fields['shipping_distrito']) {
                $errors->add('required-field', apply_filters('woocommerce_checkout_required_field_notice', sprintf(__('%s is a required field.', 'woocommerce'), '<strong>' . esc_html('Shipping Distrito') . '</strong>'), 'Shipping Distrito'));
            }
        }
    }
}

add_action( 'woocommerce_checkout_process', 'rt_remove_wc_validation', 1 );
function rt_remove_wc_validation () {
	remove_action( 'woocommerce_checkout_process', 'some_custom_checkout_field_process' );
}
