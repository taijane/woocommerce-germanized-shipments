<?php

namespace Vendidero\Germanized\Shipments;

defined( 'ABSPATH' ) || exit;

/**
 * Main package class.
 */
class Package {
    /**
     * Version.
     *
     * @var string
     */
    const VERSION = '0.0.1-dev';

    /**
     * Init the package - load the REST API Server class.
     */
    public static function init() {
	    if ( ! self::has_dependencies() ) {
		    return;
	    }

	    self::define_tables();

	    // Install
	    register_activation_hook( trailingslashit( self::get_path() ) . 'woocommerce-germanized-shipments.php', array( __CLASS__, 'install' ) );

	    if ( self::is_enabled() ) {
		    self::init_hooks();
	    }

        self::includes();
    }

    protected static function init_hooks() {
	    add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_data_stores' ), 10, 1 );
	    add_action( 'after_setup_theme', array( __CLASS__, 'include_template_functions' ), 11 );

	    // Filter email templates
	    add_filter( 'woocommerce_gzd_default_plugin_template', array( __CLASS__, 'filter_templates' ), 10, 3 );
    }

	public static function install() {
		Install::install();
	}

	public static function install_integration() {
		self::install();
	}

	public static function has_dependencies() {
		return class_exists( 'WooCommerce' );
	}

    private static function includes() {

        if ( self::is_enabled() ) {

	        if ( is_admin() ) {
		        Admin\Admin::init();
	        }

	        Ajax::init();
	        Automation::init();
	        Emails::init();
	        Validation::init();
	        Api::init();
        }

        include_once self::get_path() . '/includes/wc-gzd-shipment-functions.php';
    }

    /**
     * Function used to Init WooCommerce Template Functions - This makes them pluggable by plugins and themes.
     */
    public static function include_template_functions() {
        include_once self::get_path() . '/includes/wc-gzd-shipments-template-functions.php';
    }

    public static function filter_templates( $path, $template_name ) {

        if ( file_exists( self::get_path() . '/templates/' . $template_name ) ) {
            $path = self::get_path() . '/templates/' . $template_name;
        }

        return $path;
    }

    /**
     * Register custom tables within $wpdb object.
     */
    private static function define_tables() {
        global $wpdb;

        // List of tables without prefixes.
        $tables = array(
            'gzd_shipment_itemmeta' => 'woocommerce_gzd_shipment_itemmeta',
            'gzd_shipmentmeta'      => 'woocommerce_gzd_shipmentmeta',
            'gzd_shipments'         => 'woocommerce_gzd_shipments',
            'gzd_shipment_items'    => 'woocommerce_gzd_shipment_items',
        );

        foreach ( $tables as $name => $table ) {
            $wpdb->$name    = $wpdb->prefix . $table;
            $wpdb->tables[] = $table;
        }
    }

    public static function register_data_stores( $stores ) {
        $stores['shipment']      = 'Vendidero\Germanized\Shipments\DataStores\Shipment';
        $stores['shipment-item'] = 'Vendidero\Germanized\Shipments\DataStores\ShipmentItem';

        return $stores;
    }

    public static function test() {

        $shipments = wc_gzd_get_shipments( array(
            'date_created'    => '>2019-07-21',
        ) );

        var_dump($shipments);
        exit();

        /*
        $shipment = new WC_GZD_Shipment( 1 );
        $shipment->set_country( 'DE' );
        $shipment->set_address( '' );
        $shipment->set_order_id( 21899 );

        $item = $shipment->get_item( 1 );
        var_dump($item);

        $id = $shipment->save();

        var_dump($id);

        exit();
        */

        // $id = $shipment->save();
        // var_dump($id);
    }

    /**
     * Return the version of the package.
     *
     * @return string
     */
    public static function get_version() {
        return self::VERSION;
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_path() {
        return dirname( __DIR__ );
    }

    /**
     * Return the path to the package.
     *
     * @return string
     */
    public static function get_url() {
        return plugins_url( '', __DIR__ );
    }

    public static function get_assets_url() {
        return self::get_url() . '/assets';
    }

	public static function is_enabled() {
		return 'yes' === self::get_setting( 'enable' );
	}

	public static function get_setting( $name ) {
		$option_name = "woocommerce_gzd_shipments_{$name}";

		return get_option( $option_name );
	}
}