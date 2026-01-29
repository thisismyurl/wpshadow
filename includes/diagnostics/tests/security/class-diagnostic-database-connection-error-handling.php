<?php
/**
 * Database Connection Error Handling Diagnostic
 *
 * Tests database connection failure scenarios and error handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Connection Error Handling Class
 *
 * Tests database error handling.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Connection_Error_Handling extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-error-handling';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Error Handling';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests database connection failure scenarios and error handling';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$error_check = self::check_error_handling();
		
		if ( $error_check['has_issues'] ) {
			$issues = array();
			
			if ( ! $error_check['has_custom_error_page'] ) {
				$issues[] = __( 'No custom db-error.php (database errors expose technical details)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-connection-error-handling',
				'meta'         => array(
					'has_custom_error_page' => $error_check['has_custom_error_page'],
					'error_page_location'   => $error_check['error_page_location'],
				),
			);
		}

		return null;
	}

	/**
	 * Check error handling.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_error_handling() {
		$check = array(
			'has_issues'            => false,
			'has_custom_error_page' => false,
			'error_page_location'   => '',
		);

		// Check for custom db-error.php.
		$db_error_path = WP_CONTENT_DIR . '/db-error.php';
		
		if ( file_exists( $db_error_path ) ) {
			$check['has_custom_error_page'] = true;
			$check['error_page_location'] = $db_error_path;
		} else {
			$check['has_issues'] = true;
		}

		return $check;
	}
}
