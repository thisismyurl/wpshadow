<?php
/**
 * Import Resume and Checkpoint Capability Diagnostic
 *
 * Tests whether interrupted imports can be resumed.
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
 * Import Resume and Checkpoint Capability Diagnostic Class
 *
 * Tests whether interrupted imports can be resumed from checkpoints.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Import_Resume_And_Checkpoint_Capability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'import-resume-and-checkpoint-capability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Import Resume and Checkpoint Capability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether interrupted imports can be resumed';

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

		// Check for import checkpoint storage.
		$has_checkpoint_meta = has_filter( 'import_checkpoint' );
		if ( ! $has_checkpoint_meta ) {
			$issues[] = __( 'No checkpoint storage mechanism available', 'wpshadow' );
		}

		// Check for import state persistence.
		$import_state = get_option( 'wpshadow_import_state', array() );
		if ( empty( $import_state ) && ! is_array( $import_state ) ) {
			$issues[] = __( 'Import state not properly persisted in options', 'wpshadow' );
		}

		// Check for transient storage for progress.
		$transient_support = get_transient( 'wpshadow_test' );
		// Transients should work - set and retrieve.
		set_transient( 'wpshadow_test', 'working', 60 );
		$transient_test = get_transient( 'wpshadow_test' );

		if ( $transient_test !== 'working' ) {
			$issues[] = __( 'Transient storage not working - cannot track import progress', 'wpshadow' );
		}

		delete_transient( 'wpshadow_test' );

		// Check for batch processing capability.
		$has_batch_filter = has_filter( 'import_batch_size' );
		if ( ! $has_batch_filter ) {
			$issues[] = __( 'No batch processing support - cannot resume from checkpoints', 'wpshadow' );
		}

		// Check for REST API resume endpoint.
		$rest_routes = array();
		if ( function_exists( 'rest_get_server' ) ) {
			$rest_server = rest_get_server();
			if ( is_object( $rest_server ) && method_exists( $rest_server, 'get_routes' ) ) {
				$rest_routes = $rest_server->get_routes();
			}
		}
		$has_resume_endpoint = false;

		foreach ( (array) $rest_routes as $route => $methods ) {
			if ( stripos( $route, 'import' ) !== false && stripos( $route, 'resume' ) !== false ) {
				$has_resume_endpoint = true;
				break;
			}
		}

		if ( ! $has_resume_endpoint ) {
			$issues[] = __( 'No REST API resume endpoint available', 'wpshadow' );
		}

		// Check for item index tracking.
		$sample_import = get_option( '_sample_import_index' );
		if ( ! isset( $sample_import ) ) {
			// This is fine - just checking structure.
		}

		// Check for interruption recovery mechanism.
		if ( ! has_action( 'import_interrupted' ) && ! has_action( 'shutdown' ) ) {
			$issues[] = __( 'No interruption recovery mechanism registered', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/import-resume-and-checkpoint-capability',
			);
		}

		return null;
	}
}
