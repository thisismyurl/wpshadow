<?php
/**
 * 404 Error Page Functionality Diagnostic
 *
 * Validates that the 404 error page is properly configured with appropriate
 * template, search functionality, and helpful content for users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 404 Error Page Functionality Diagnostic Class
 *
 * Checks 404 template implementation and configuration.
 *
 * @since 1.6032.1300
 */
class Diagnostic_404_Error_Page_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '404-error-page-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Page Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates 404 error page configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1300
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$template_dir = get_template_directory();

		// Check for 404.php template.
		$error_404_file = $template_dir . '/404.php';
		if ( ! file_exists( $error_404_file ) ) {
			$issues[] = __( 'Missing 404.php template (falls back to index.php)', 'wpshadow' );
		} else {
			$content = file_get_contents( $error_404_file );

			// Check for search form.
			if ( false === stripos( $content, 'get_search_form' ) && false === stripos( $content, '<form' ) && false === stripos( $content, 'role="search"' ) ) {
				$issues[] = __( '404 template lacks search form (users cannot find content)', 'wpshadow' );
			}

			// Check for helpful navigation.
			$helpful_elements = array(
				'wp_nav_menu'      => false,
				'get_recent_posts' => false,
				'wp_list_pages'    => false,
				'get_archives'     => false,
			);

			foreach ( $helpful_elements as $element => $found ) {
				if ( false !== stripos( $content, $element ) ) {
					$helpful_elements[ $element ] = true;
				}
			}

			if ( ! in_array( true, $helpful_elements, true ) ) {
				$issues[] = __( '404 template lacks helpful navigation (no menus, recent posts, or archives)', 'wpshadow' );
			}

			// Check content length (should be substantial).
			if ( strlen( $content ) < 500 ) {
				$issues[] = __( '404 template is very minimal (consider adding more helpful content)', 'wpshadow' );
			}
		}

		// Check for 404 redirect plugins (can mask underlying issues).
		$redirect_plugins = array(
			'redirection/redirection.php',
			'404-solution/404solution.php',
			'404-to-301/404-to-301.php',
		);

		$has_404_redirect = false;
		foreach ( $redirect_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_404_redirect = true;
				break;
			}
		}

		if ( $has_404_redirect ) {
			$issues[] = __( '404 redirect plugin active (may hide broken links)', 'wpshadow' );
		}

		// Check recent 404 errors.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpshadow_404_log';

		// Only check if log table exists.
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
			$recent_404s = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table_name} WHERE date > DATE_SUB(NOW(), INTERVAL %d DAY)",
					7
				)
			);

			if ( $recent_404s > 100 ) {
				$issues[] = sprintf(
					/* translators: %d: number of 404 errors */
					__( '%d 404 errors in the last 7 days (investigate broken links)', 'wpshadow' ),
					$recent_404s
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of 404 page issues */
					__( 'Found %d issues with 404 error page configuration.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Ensure 404.php includes search form, helpful navigation, and user-friendly content.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
