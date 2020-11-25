<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


add_action('admin_menu', 'rt_ubigeo_register_admin_page');

function rt_ubigeo_register_admin_page()
{
    add_submenu_page('woocommerce', 'Configuraciones', 'Ubigeo Perú', 'manage_woocommerce', 'rt_ubigeo_settings', 'rt_ubigeo_submenu_settings_callback');
    add_action('admin_init', 'rt_ubigeo_register_settings');
}

function rt_ubigeo_submenu_settings_callback()
{
    if (isset($_REQUEST["settings-updated"]) && sanitize_text_field($_REQUEST["settings-updated"] == true)) {
        echo "<script>alert('Se han guardado la nuevas opciones.');</script>";
    } ?>
    
    <div class="wrap woocommerce" id="facto-conf">
        <div style="background-color:#87b43e;">
        </div>
        <h1><?php _e("Ubigeo from Peru for Woocommerce | Integration of Ubigeo from Peru to your Woocommerce", 'ubigeo-peru') ?></h1>
        <hr>
        <h2 class="nav-tab-wrapper">
            <a href="?page=rt_ubigeo_settings&tab=docs" class="nav-tab <?php
            if ((!isset($_REQUEST['tab'])) || ($_REQUEST['tab'] == "docs")) {
                print " nav-tab-active";
            } ?>"><?php echo     _e('Docs', 'ubigeo-peru') ?></a>
               <?php
               if (rt_costo_ubigeo_plugin_enabled()) {
                   ?>
                <a href="?page=rt_ubigeo_settings&tab=cost" class="nav-tab <?php
                if ($_REQUEST['tab'] == "cost") {
                    print " nav-tab-active";
                } ?>"><?php _e('Ubigeo', 'ubigeo-peru') ?></a>
                <a href="?page=rt_ubigeo_settings&tab=import" class="nav-tab <?php
                if ($_REQUEST['tab'] == "import") {
                    print " nav-tab-active";
                } ?>"><?php _e('Import', 'ubigeo-peru') ?></a>
                <a href="?page=rt_ubigeo_settings&tab=license" class="nav-tab <?php
                if ($_REQUEST['tab'] == "license") {
                    print " nav-tab-active";
                } ?>"><?php _e('License', 'ubigeo-peru') ?></a>
               <?php
               } ?>
            <a href="?page=rt_ubigeo_settings&tab=help" class="nav-tab <?php
               if ($_REQUEST['tab'] == "help") {
                   print " nav-tab-active";
               } ?>"><?php _e('Help', 'ubigeo-peru') ?></a>

        </h2>
        <?php
        if ((!isset($_REQUEST['tab'])) || ($_REQUEST['tab'] == "docs")) {
            rt_ubigeo_submenu_settings_docs();
        } elseif ($_REQUEST['tab'] == "cost") {
            if (rt_costo_ubigeo_plugin_enabled()) {
                if (isset($_REQUEST['section']) == "ubigeo") {
                    if (isset($_REQUEST['list_cost']) == "new") {
                        rt_ubigeo_submenu_settings_cost_new();
                    } elseif (isset($_REQUEST['edit'])) {
                        rt_ubigeo_submenu_settings_cost_edit($_REQUEST['edit']);
                    } else {
                        rt_ubigeo_submenu_settings_cost_ubigeo();
                    }
                } else {
                    rt_ubigeo_submenu_settings_cost();
                }
            }
        } elseif ($_REQUEST['tab'] == "import") {
            rt_ubigeo_submenu_settings_import();
        } elseif ($_REQUEST['tab'] == "license") {
            rt_ubigeo_submenu_settings_license();
        } elseif ($_REQUEST['tab'] == "help") {
            if (rt_costo_ubigeo_plugin_enabled()) {
                rt_ubigeo_submenu_settings_help_cost();
            } else {
                rt_ubigeo_submenu_settings_help();
            }
        } ?>
    </div>
    <?php
}

function rt_ubigeo_register_settings()
{
    // Comprobar que existan las tablas necesarias
    global $wpdb;

    $sql = 'show tables like "' . $wpdb->prefix . 'ubigeo_departamento"';
    $resultado = $wpdb->query($sql);

    if ($resultado == 0) {
        add_action('admin_notices', 'rt_ubigeo_errortabledep');
    }

    $sql = 'show tables like "' . $wpdb->prefix . 'ubigeo_provincia"';
    $resultado = $wpdb->query($sql);

    if ($resultado == 0) {
        add_action('admin_notices', 'rt_ubigeo_errortableprov');
    }

    $sql = 'show tables like "' . $wpdb->prefix . 'ubigeo_distrito"';
    $resultado = $wpdb->query($sql);

    if ($resultado == 0) {
        add_action('admin_notices', 'rt_ubigeo_errortabledist');
    }

    //para cada mp obtenemos su configuraci&oacute;n
    if (class_exists('woocommerce')) {
        if (!mercado_pago_plugin_enabled()) {
            $mp_arr = WC()->payment_gateways->get_available_payment_gateways();
        } else {
            $mp_arr = array();
        }
    } else {
        add_action('admin_notices', 'rt_ubigeo_errornowoocommerce');

        $mp_arr = array();
    }
}

function mercado_pago_plugin_enabled()
{
    if (in_array('woocommerce-mercadopago/woocommerce-mercadopago.php', (array) get_option('active_plugins', array()))) {
        return true;
    }
    return false;
}

function rt_ubigeo_submenu_settings_docs()
{
    ?>
    <h1><?php _e("Peru Ubigeo Documentation for Woocommerce", 'ubigeo-peru'); ?></h1>
    <div>
        <div>
            <h3><?php _e("Description", 'ubigeo-peru'); ?></h3>
        </div>
        <div>
            <p><?php _e('Allows you to select departments, provinces and districts of Peru', 'ubigeo-peru'); ?></p>
        </div>
    </div>
    <div>
        <div>
            <h3><?php _e('Attributes', 'ubigeo-peru'); ?></h3>
        </div>
        <div>
            <ul>
                <li><?php _e('Add the Departments of Peru', 'ubigeo-peru'); ?></li>
                <li><?php _e('Add the Provinces of Peru', 'ubigeo-peru'); ?></li>
                <li><?php _e('Add the Districts of Peru', 'ubigeo-peru'); ?></li>
            </ul>
        </div>
    </div>
    <?php if (rt_costo_ubigeo_plugin_enabled()) { ?>
    <div>
        <div>
            <h3><?php _e("Activate shipping ubigeo", 'ubigeo-peru'); ?></h3>
        </div>
        <div>
            <p><?php _e('You can go to the following link', 'ubigeo-peru'); ?> <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=shipping&section=costo_ubigeo_peru_shipping_method') ?>" ><?php _e('Shipping Ubigeo ', 'ubigeo-peru'); ?></a>.</p>
        </div>
    </div>
    <div>
        <div>
            <h3><?php _e("Link Docs Ubigeo", 'ubigeo-peru'); ?></h3>
        </div>
        <div>
            <p><?php _e('You can read the documentation in the ', 'ubigeo-peru'); ?> <a href="https://renzotejada.com/documentacion/docs-costo-de-envio-de-ubigeo-de-peru-para-woocommerce/?url=dashboard-wodpress" target="_blank" ><?php  _e('Docs ', 'ubigeo-peru'); ?></a>.</p>
        </div>
    </div>
    <?php
    }
}

function rt_ubigeo_submenu_settings_help()
{
    ?>
    <h2><?php _e('Help', 'ubigeo-peru'); ?></h2>

    <h3><?php _e('What does this module do?', 'ubigeo-peru'); ?></h3>

    <p><?php _e('It allows you to integrate your Woocommerce to Ubigeo from Peru to ask customers for their information at checkout.', 'ubigeo-peru'); ?></p>

    <h3><?php _e('What is the cost of the module?', 'ubigeo-peru'); ?></h3>

    <p><?php _e('This plugin is totally free.', 'ubigeo-peru'); ?></p>

    <h3><?php _e('How do I add the shipping cost or configure the costs?', 'ubigeo-peru'); ?></h3>
    
    <p><?php _e('This plugin only adds the ubigeo in the woocommerce checkout, it does not have the shipping cost functionality', 'ubigeo-peru'); ?></p>

    <h3><?php _e('How to configure the shipping cost to the module?', 'ubigeo-peru'); ?></h3>

    <p><?php _e('Said functional functionality is in a PREMIUM version.', 'ubigeo-peru'); ?></p>

    <h3><?php _e('What is the PREMIUM version?', 'ubigeo-peru'); ?></h3>

    <p><?php _e('The PREMIUM version is in the following ', 'ubigeo-peru'); ?><a href="https://renzotejada.com/plugin/costo-de-envio-de-ubigeo-de-peru-para-woocommerce/" target="_blank"><?php _e('LINK', 'ubigeo-peru'); ?></a>.</p>

    <h3><?php _e('I have other questions', 'ubigeo-peru'); ?></h3>

    <p><?php _e('Go to', 'ubigeo-peru'); ?> <a href="https://renzotejada.com/contacto?url=dashboard-wodpress" target="_blank"><?php _e('RT - Contact', 'ubigeo-peru'); ?></a></p>
    <?php
}
