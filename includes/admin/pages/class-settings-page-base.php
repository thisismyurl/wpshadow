<?php
/**
 * Settings Page Base Class
 *
 * Provides common functionality for admin settings pages.
 *
 * @package    WPShadow
 * @subpackage Admin\Pages
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings_Page_Base Class
 *
 * Base class for admin settings pages providing common properties and methods.
 *
 * @since 0.6093.1200
 */
abstract class Settings_Page_Base {

	/**
	 * Page slug
	 *
	 * @var string
	 */
	protected $page_slug = '';

	/**
	 * Menu parent
	 *
	 * @var string
	 */
	protected $menu_parent = '';

	/**
	 * Page title
	 *
	 * @var string
	 */
	protected $page_title = '';

	/**
	 * Menu title
	 *
	 * @var string
	 */
	protected $menu_title = '';

	/**
	 * Required capability
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	/**
	 * Constructor
	 *
	 * @since 0.6093.1200
	 */
	public function __construct() {
		// Default menu title to page title if not set
		if ( empty( $this->menu_title ) ) {
			$this->menu_title = $this->page_title;
		}

		// Register the page with WordPress
		add_action( 'admin_menu', array( $this, 'register_page' ), 20 );
	}

	/**
	 * Register the page with WordPress admin menu
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public function register_page(): void {
		if ( empty( $this->page_slug ) || empty( $this->page_title ) ) {
			return;
		}

		if ( ! empty( $this->menu_parent ) ) {
			add_submenu_page(
				$this->menu_parent,
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->page_slug,
				array( $this, 'render' )
			);
		} else {
			add_menu_page(
				$this->page_title,
				$this->menu_title,
				$this->capability,
				$this->page_slug,
				array( $this, 'render' )
			);
		}
	}

	/**
	 * Render the page content
	 *
	 * Must be implemented by child classes.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	abstract public function render(): void;

	/**
	 * Check if current user has permission to view this page
	 *
	 * @since 0.6093.1200
	 * @return bool True if user has permission.
	 */
	protected function can_view(): bool {
		return current_user_can( $this->capability );
	}

	/**
	 * Render permission denied message
	 *
	 * @since 0.6093.1200
	 * @return void Dies with error message.
	 */
	protected function render_permission_denied(): void {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'wpshadow' ) );
	}
}
