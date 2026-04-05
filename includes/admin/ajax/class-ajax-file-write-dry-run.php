<?php
/**
 * File Write Dry-Run AJAX Handler
 *
 * Calls the treatment's static dry_run() method (or falls back to the base
 * execute() dry-run mode) and returns a structured diff for the UI to render.
 * Nothing is written to disk.
 *
 * Response shape:
 *   {
 *     "current_snippet": "...",   // relevant portion of current file
 *     "proposed_snippet": "...",  // what would be inserted/changed
 *     "diff_lines": [             // line-by-line diff for rendering
 *       { "type": "context|add|remove", "content": "..." }
 *     ],
 *     "message": "..."
 *   }
 *
 * @package WPShadow
 * @subpackage Admin\Ajax
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Admin\File_Write_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Executes a non-destructive dry-run preview of a file-write treatment.
 */
class Ajax_File_Write_Dry_Run extends AJAX_Handler_Base {

	/**
	 * Register WordPress AJAX action.
	 *
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_file_write_dry_run', [ __CLASS__, 'handle' ] );
	}

	/**
	 * Handle dry-run request.
	 *
	 * POST params:
	 *   nonce       (string) wpshadow_file_write_dry_run nonce
	 *   finding_id  (string) treatment finding ID
	 *
	 * @return void
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_file_write_dry_run', 'manage_options', 'nonce' );

		$finding_id = self::get_post_param( 'finding_id', 'text', '', true );

		$info = self::get_treatment_info( $finding_id );
		if ( ! $info ) {
			self::send_error( __( 'Unknown treatment.', 'wpshadow' ) );
		}

		$class     = $info['class'];
		$file_path = self::assert_allowed_managed_file_path( (string) $info['target_file'] );

		// Read current file content (or use empty string if file is new).
		$current_content = '';
		if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
			$current_content = (string) self::read_wp_filesystem_file( $file_path );
		}

		// If treatment implements dry_run(), call it directly.
		if ( method_exists( $class, 'dry_run' ) ) {
			$dry_result = $class::dry_run();
		} else {
			// Fallback: use base execute() dry-run mode.
			$dry_result = $class::execute( true );
		}

		$proposed_snippet = $info['snippet'];

		// Build a simple line-level unified diff.
		$diff_lines = self::build_diff(
			$current_content,
			$proposed_snippet,
			$file_path
		);

		\WPShadow\Core\Activity_Logger::log(
			'file_dry_run',
			/* translators: %s: finding ID */
			sprintf( __( 'Dry-run executed for: %s', 'wpshadow' ), $finding_id ),
			'security',
			[ 'finding_id' => $finding_id ]
		);

		self::send_success( [
			'current_snippet'  => wp_trim_words( $current_content, 60, '… (truncated)' ),
			'proposed_snippet' => $proposed_snippet,
			'diff_lines'       => $diff_lines,
			'dry_run_result'   => $dry_result,
			'message'          => __( 'Preview generated. No changes have been made.', 'wpshadow' ),
		] );
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Resolve treatment info from registry by finding ID.
	 *
	 * @param string $finding_id
	 * @return array|null
	 */
	private static function get_treatment_info( string $finding_id ): ?array {
		foreach ( File_Write_Registry::get_all() as $class ) {
			if ( class_exists( $class ) && method_exists( $class, 'get_finding_id' ) ) {
				if ( $class::get_finding_id() === $finding_id ) {
					return File_Write_Registry::get_treatment_info( $class );
				}
			}
		}
		return null;
	}

	/**
	 * Build a line-by-line diff showing the proposed snippet in context.
	 *
	 * We show up to 5 lines of context around the insertion point and
	 * highlight the new lines with a '+' prefix. Since file-write treatments
	 * only append or replace specific blocks, this is enough for a clear preview.
	 *
	 * @param string $current_content Full current file content.
	 * @param string $snippet         Content being inserted.
	 * @param string $file_path       File path (used only for labelling).
	 * @return array[] Each element: [ 'type' => 'context|add|remove', 'content' => string ]
	 */
	private static function build_diff( string $current_content, string $snippet, string $file_path ): array {
		$lines  = [];
		$label  = basename( $file_path );

		// Context: first 5 lines of the current file.
		$current_lines = array_slice( explode( "\n", $current_content ), 0, 6 );
		foreach ( $current_lines as $line ) {
			$lines[] = [ 'type' => 'context', 'content' => $line ];
		}

		if ( count( explode( "\n", $current_content ) ) > 6 ) {
			$lines[] = [ 'type' => 'context', 'content' => '...' ];
		}

		// Addition: the snippet lines.
		foreach ( explode( "\n", $snippet ) as $line ) {
			$lines[] = [ 'type' => 'add', 'content' => $line ];
		}

		return $lines;
	}
}
