<?php
/**
 * Operation Progress Not Persisting Diagnostic
 *
 * Tests for progress tracking persistence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Operation Progress Not Persisting Diagnostic Class
 *
 * Tests for progress tracking persistence during operations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Operation_Progress_Not_Persisting extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'operation-progress-not-persisting';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Operation Progress Not Persisting';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for progress tracking persistence';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check transient persistence.
		$test_transient = '_wpshadow_progress_test_' . time();
		set_transient( $test_transient, array( 'progress' => 50 ), HOUR_IN_SECONDS );
		$retrieved = get_transient( $test_transient );

		if ( $retrieved === false ) {
			$issues[] = __( 'Transients not persisting - progress tracking will fail', 'wpshadow' );
		} else {
			delete_transient( $test_transient );
		}

		// Check for progress logging mechanism.
		if ( ! has_action( 'wpshadow_operation_progress_update' ) ) {
			$issues[] = __( 'No progress update hook available', 'wpshadow' );
		}

		// Check options API persistence.
		$test_option = '_wpshadow_op_progress_' . time();
		update_option( $test_option, array( 'processed' => 100, 'total' => 1000 ) );
		$retrieved_option = get_option( $test_option );

		if ( empty( $retrieved_option ) ) {
			$issues[] = __( 'Options not persisting properly - progress not saved', 'wpshadow' );
		} else {
			delete_option( $test_option );
		}

		// Check for progress storage mechanism.
		if ( false === has_filter( 'wpshadow_save_progress_to_option' ) && false === has_filter( 'wpshadow_save_progress_to_meta' ) ) {
			$issues[] = __( 'No explicit progress persistence hooks found for options or metadata', 'wpshadow' );
		}

		// Check for object cache enabling progress.
		$has_cache = false;
		if ( function_exists( 'wp_using_ext_object_cache' ) ) {
			$has_cache = wp_using_ext_object_cache();
		} elseif ( function_exists( 'wp_cache_get' ) ) {
			$has_cache = true;
		}

		if ( ! $has_cache ) {
			$issues[] = __( 'Object cache not enabled - progress tracking will be slow', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/operation-progress-not-persisting?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
