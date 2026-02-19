<?php
/**
 * Block Registry
 *
 * Centralized registration and management of all WPShadow custom blocks.
 *
 * @package    WPShadow
 * @subpackage Blocks
 * @since      1.6034.1500
 */

declare(strict_types=1);

namespace WPShadow\Blocks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block_Registry Class
 *
 * Manages registration and initialization of all custom Gutenberg blocks.
 *
 * @since 1.6034.1500
 */
class Block_Registry {

	/**
	 * Registered blocks.
	 *
	 * @var array
	 */
	private static $blocks = array();

	/**
	 * Initialize block registry.
	 *
	 * @since 1.6034.1500
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_blocks' ) );
		add_action( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_editor_assets' ) );
		add_action( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ) );
		add_filter( 'block_categories_all', array( __CLASS__, 'add_block_category' ), 10, 2 );
	}

	/**
	 * Register all custom blocks.
	 *
	 * @since 1.6034.1500
	 * @return void
	 */
	public static function register_blocks() {
		// Register block classes.
		self::$blocks = array(
			'pricing-table'     => Pricing_Table_Block::class,
			'faq-accordion'     => FAQ_Accordion_Block::class,
			'cta-block'         => CTA_Block::class,
			'icon-box'          => Icon_Box_Block::class,
			'timeline'          => Timeline_Block::class,
			'before-after'      => Before_After_Block::class,
			'stats-counter'     => Stats_Counter_Block::class,
			'logo-grid'         => Logo_Grid_Block::class,
			'countdown-timer'   => Countdown_Timer_Block::class,
			'content-tabs'      => Content_Tabs_Block::class,
			'alert-notice'      => Alert_Notice_Block::class,
			'progress-bar'      => Progress_Bar_Block::class,
		);

		// Initialize each block.
		foreach ( self::$blocks as $slug => $class ) {
			if ( class_exists( $class ) && method_exists( $class, 'register' ) ) {
				call_user_func( array( $class, 'register' ) );
			}
		}
	}

	/**
	 * Enqueue editor assets.
	 *
	 * @since 1.6034.1500
	 * @return void
	 */
	public static function enqueue_editor_assets() {
		// Editor JavaScript.
		wp_enqueue_script(
			'wpshadow-blocks-editor',
			WPSHADOW_URL . 'assets/js/blocks/editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n' ),
			WPSHADOW_VERSION,
			true
		);

		// Modal block editor JavaScript.
		wp_enqueue_script(
			'wpshadow-modal-block-editor',
			WPSHADOW_URL . 'assets/js/blocks/modal-block.js',
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			WPSHADOW_VERSION,
			true
		);

		// Editor styles.
		wp_enqueue_style(
			'wpshadow-blocks-editor',
			WPSHADOW_URL . 'assets/css/blocks/editor.css',
			array( 'wp-edit-blocks' ),
			WPSHADOW_VERSION
		);

		// Localize script data.
		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-blocks-editor',
			'wpShadowBlocks',
			'wpshadow_blocks',
			array(
				'blocks'      => array_keys( self::$blocks ),
				'blockPrefix' => 'wpshadow',
			)
		);
	}

	/**
	 * Enqueue block assets (frontend + editor).
	 *
	 * @since 1.6034.1500
	 * @return void
	 */
	public static function enqueue_block_assets() {
		// Frontend styles.
		wp_enqueue_style(
			'wpshadow-blocks',
			WPSHADOW_URL . 'assets/css/blocks/blocks.css',
			array(),
			WPSHADOW_VERSION
		);

		// Frontend JavaScript.
		wp_enqueue_script(
			'wpshadow-blocks-frontend',
			WPSHADOW_URL . 'assets/js/blocks/frontend.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Modal styles.
		wp_enqueue_style(
			'wpshadow-modal',
			WPSHADOW_URL . 'assets/css/modal.css',
			array(),
			WPSHADOW_VERSION
		);

		// Modal JavaScript.
		wp_enqueue_script(
			'wpshadow-modal-handler',
			WPSHADOW_URL . 'assets/js/modal-handler.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);
	}

	/**
	 * Add custom block category.
	 *
	 * @since  1.6034.1500
	 * @param  array                   $categories Existing categories.
	 * @param  \WP_Block_Editor_Context $context    Block editor context.
	 * @return array Modified categories.
	 */
	public static function add_block_category( $categories, $context ) {
		return array_merge(
			array(
				array(
					'slug'  => 'wpshadow',
					'title' => __( 'WPShadow', 'wpshadow' ),
					'icon'  => 'shield-alt',
				),
			),
			$categories
		);
	}

	/**
	 * Get registered blocks.
	 *
	 * @since  1.6034.1500
	 * @return array Registered block classes keyed by slug.
	 */
	public static function get_blocks() {
		return self::$blocks;
	}

	/**
	 * Check if block is registered.
	 *
	 * @since  1.6034.1500
	 * @param  string $slug Block slug.
	 * @return bool True if registered.
	 */
	public static function is_registered( $slug ) {
		return isset( self::$blocks[ $slug ] );
	}
}
