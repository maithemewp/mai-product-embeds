<?php

/**
 * Plugin Name:     Mai Product Embeds
 * Plugin URI:      https://bizbudding.com
 * Description:     Easily embed Amazon (and more) products into your posts with Mai Post Grid.
 * Version:         0.1.0
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Must be at the top of the file.
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Main Mai_Product_Embeds_Plugin Class.
 *
 * @since 0.1.0
 */
final class Mai_Product_Embeds_Plugin {

	/**
	 * @var   Mai_Product_Embeds_Plugin The one true Mai_Product_Embeds_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Product_Embeds_Plugin Instance.
	 *
	 * Insures that only one instance of Mai_Product_Embeds_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Product_Embeds_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Mai_Product_Embeds_Plugin::includes() Include the required files.
	 * @uses    Mai_Product_Embeds_Plugin::hooks() Activate, deactivate, etc.
	 * @see     Mai_Product_Embeds_Plugin()
	 * @return  object | Mai_Product_Embeds_Plugin The one true Mai_Product_Embeds_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Product_Embeds_Plugin;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-product-embeds' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'mai-product-embeds' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'MAI_PRODUCT_EMBEDS_VERSION' ) ) {
			define( 'MAI_PRODUCT_EMBEDS_VERSION', '0.1.0' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_PRODUCT_EMBEDS_DIR' ) ) {
			define( 'MAI_PRODUCT_EMBEDS_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Includes Path.
		if ( ! defined( 'MAI_PRODUCT_EMBEDS_INCLUDES_DIR' ) ) {
			define( 'MAI_PRODUCT_EMBEDS_INCLUDES_DIR', MAI_PRODUCT_EMBEDS_DIR . 'includes/' );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';
		// Includes.
		// foreach ( glob( MAI_PRODUCT_EMBEDS_DIR . 'includes/*.php' ) as $file ) { include $file; }
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'plugins_loaded',      [ $this, 'updater' ] );
		add_action( 'init',                [ $this, 'register_content_types' ] );
		add_filter( 'mai_grid_post_types', [ $this, 'post_types' ] );

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {
		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = PucFactory::buildUpdateChecker( 'https://github.com/maithemewp/mai-product-embeds/', __FILE__, 'mai-product-embeds' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}

	/**
	 * Register content types.
	 *
	 * @return  void
	 */
	public function register_content_types() {

		/***********************
		 *  Custom Post Types  *
		 ***********************/

		 register_post_type( 'mai_embed', [
			'exclude_from_search' => true,
			'has_archive'         => false,
			'hierarchical'        => false,
			'labels'              => [
				'name'               => _x( 'Product Embeds', 'Embed general name', 'mai-product-embeds' ),
				'singular_name'      => _x( 'Product Embed', 'Embed singular name', 'mai-product-embeds' ),
				'menu_name'          => _x( 'Product Embeds', 'Embed admin menu', 'mai-product-embeds' ),
				'name_admin_bar'     => _x( 'Product Embed', 'Embed add new on admin bar', 'mai-product-embeds' ),
				'add_new'            => _x( 'Add New', 'Embed', 'mai-product-embeds' ),
				'add_new_item'       => __( 'Add New Embed',  'mai-product-embeds' ),
				'new_item'           => __( 'New Embed', 'mai-product-embeds' ),
				'edit_item'          => __( 'Edit Embed', 'mai-product-embeds' ),
				'view_item'          => __( 'View Embed', 'mai-product-embeds' ),
				'all_items'          => __( 'All Embeds', 'mai-product-embeds' ),
				'search_items'       => __( 'Search Embeds', 'mai-product-embeds' ),
				'parent_item_colon'  => __( 'Parent Embeds:', 'mai-product-embeds' ),
				'not_found'          => __( 'No Embeds found.', 'mai-product-embeds' ),
				'not_found_in_trash' => __( 'No Embeds found in Trash.', 'mai-product-embeds' )
			],
			'menu_icon'          => 'dashicons-html',
			'public'             => false,
			'publicly_queryable' => false,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => false,
			'show_in_rest'       => true,
			'show_ui'            => true,
			'rewrite'            => false,
			'supports'           => [ 'title', 'editor' ],
			'template_lock'      => 'all',
			'template'           => [
				[ 'core/html', [] ],
			],
		] );

		/***********************
		 *  Custom Taxonomies  *
		 ***********************/

		 register_taxonomy( 'mai_embed_cat', [ 'mai_embed' ], [
			'hierarchical'               => true,
			'labels'                     => [
				'name'                       => _x( 'Embed Categories', 'Embed Category General Name', 'mai-product-embeds' ),
				'singular_name'              => _x( 'Embed Category', 'Embed Category Singular Name', 'mai-product-embeds' ),
				'menu_name'                  => __( 'Embed Categories', 'mai-product-embeds' ),
				'all_items'                  => __( 'All Items', 'mai-product-embeds' ),
				'parent_item'                => __( 'Parent Item', 'mai-product-embeds' ),
				'parent_item_colon'          => __( 'Parent Item:', 'mai-product-embeds' ),
				'new_item_name'              => __( 'New Item Name', 'mai-product-embeds' ),
				'add_new_item'               => __( 'Add New Item', 'mai-product-embeds' ),
				'edit_item'                  => __( 'Edit Item', 'mai-product-embeds' ),
				'update_item'                => __( 'Update Item', 'mai-product-embeds' ),
				'view_item'                  => __( 'View Item', 'mai-product-embeds' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'mai-product-embeds' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'mai-product-embeds' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'mai-product-embeds' ),
				'popular_items'              => __( 'Popular Items', 'mai-product-embeds' ),
				'search_items'               => __( 'Search Items', 'mai-product-embeds' ),
				'not_found'                  => __( 'Not Found', 'mai-product-embeds' ),
			],
			'meta_box_cb'                => null, // Set false to hide metabox.
			'public'                     => false,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_in_rest'               => true,
			'show_in_quick_edit'         => true,
			'show_tagcloud'              => false,
			'show_ui'                    => true,
			'rewrite'                    => false,
		] );
	}

	/**
	 * Adds embed to the available grid post types.
	 *
	 * @since 0.1.0
	 *
	 * @param array $post_types The post types.
	 *
	 * @return array
	 */
	function post_types( $post_types ) {
		$post_types[] = 'mai_embed';

		return array_unique( $post_types );
	}

	/**
	 * Plugin activation.
	 *
	 * @return  void
	 */
	public function activate() {
		$this->register_content_types();
		flush_rewrite_rules();
	}
}

/**
 * The main function for that returns Mai_Product_Embeds_Plugin
 *
 * The main function responsible for returning the one true Mai_Product_Embeds_Plugin
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $plugin = Mai_Product_Embeds_Plugin(); ?>
 *
 * @since 0.1.0
 *
 * @return object|Mai_Product_Embeds_Plugin The one true Mai_Product_Embeds_Plugin Instance.
 */
function mai_product_embeds_plugin() {
	return Mai_Product_Embeds_Plugin::instance();
}

// Get Mai_Product_Embeds_Plugin Running.
mai_product_embeds_plugin();
