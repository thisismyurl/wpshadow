<?php
/**
 * Import Progress Indicators Diagnostic
 *
 * Tests whether import progress is visible to users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Import Progress Indicators Diagnostic Class
 *
 * Tests whether import progress indicators and feedback are visible during imports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Import_Progress_Indicators extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-progress-indicators';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Progress Indicators';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether import progress is visible to users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for importer admin page registration.
		$has_importer_page = has_filter( 'import_begin' );
		if ( ! $has_importer_page ) {
			$issues[] = __( 'No import handler registered', 'wpshadow' );
		}

		// Check for progress indicator hooks.
		$has_progress_action = has_action( 'import_start' );
		$has_complete_action = has_action( 'import_end' );

		if ( ! $has_progress_action ) {
			$issues[] = __( 'No import start notification hook detected', 'wpshadow' );
		}

		if ( ! $has_complete_action ) {
			$issues[] = __( 'No import end notification hook detected', 'wpshadow' );
		}

		// Check for REST API progress endpoints.
		$rest_server = rest_get_server();
		$has_import_progress = false;

		if ( $rest_server && is_callable( array( $rest_server, 'get_routes' ) ) ) {
			$rest_routes = $rest_server->get_routes();

			foreach ( $rest_routes as $route => $methods ) {
				if ( stripos( $route, 'import' ) !== false && stripos( $route, 'progress' ) !== false ) {
					$has_import_progress = true;
					break;
				}
			}
		}

		if ( ! $has_import_progress ) {
			$issues[] = __( 'No REST API progress tracking endpoint available', 'wpshadow' );
		}

		// Check for AJAX progress endpoints.
		$has_ajax_action = has_action( 'wp_ajax_import_progress' );
		if ( ! $has_ajax_action ) {
			$issues[] = __( 'No AJAX import progress action registered', 'wpshadow' );
		}

		// Check for import feedback JavaScript.
		global $wp_scripts;
		$has_import_script = false;

		if ( ! empty( $wp_scripts ) && is_a( $wp_scripts, 'WP_Scripts' ) ) {
			foreach ( $wp_scripts->registered as $script ) {
				if ( stripos( $script->handle, 'import' ) !== false ) {
					$has_import_script = true;
					break;
				}
			}
		}

		if ( ! $has_import_script ) {
			$issues[] = __( 'No import progress JavaScript detected', 'wpshadow' );
		}

		// Check for accessibility in progress display.
		if ( ! has_filter( 'wp_a11y_speak' ) ) {
			$issues[] = __( 'No accessibility announcements for import progress', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-progress-indicators',
			);
		}

		return null;
	}
}
