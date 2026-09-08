<?php
/**
 * File Write Treatment Registry
 *
 * Central registry for treatments that require writing to filesystem files
 * (wp-config.php, .htaccess). Provides the data layer for the review page,
 * admin notice, and AJAX handlers.
 *
 * Each file-write treatment self-registers on load. The registry tracks
 * which findings are currently active so the notice and review page can
 * display only actionable items.
 *
 * Philosophy: Commandment #8 (Inspire Confidence) — the admin always knows
 * exactly what will be written before anything touches a file.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 0.6095
 */

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registry for file-write treatments.
 *
 * Treatment classes that write to filesystem files register themselves here.
 * The interface they must implement:
 *   - static get_finding_id(): string
 *   - static get_target_file(): string           — absolute path to target file
 *   - static get_file_label(): string            — human-friendly label e.g. "wp-config.php"
 *   - static get_proposed_change_summary(): string — one-line description
 *   - static get_proposed_snippet(): string      — the exact text to be inserted/changed
 *   - static get_sftp_undo_instructions(): string — step-by-step SFTP recovery
 *   - static get_risk_level(): string            — always 'high' for file writes
 */
class File_Write_Registry {

	/**
	 * Registered treatment class names.
	 *
	 * @var string[]
	 */
	private static array $treatments = [];

	/**
	 * Register a file-write treatment class.
	 *
	 * Called from each treatment's static initializer.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return void
	 */
	public static function register( string $class ): void {
		if ( ! in_array( $class, self::$treatments, true ) ) {
			self::$treatments[] = $class;
		}
	}

	/**
	 * Get all registered treatment class names.
	 *
	 * @return string[]
	 */
	public static function get_all(): array {
		return self::$treatments;
	}

	/**
	 * Get treatments whose finding is currently active (failing).
	 *
	 * Queries the findings cache/DB to filter down to only treatments
	 * the admin actually needs to act on.
	 *
	 * Triggers Treatment_Registry to load all treatment files if the internal
	 * registry is empty — this ensures treatments registered via boot() are
	 * available even when treatment files haven't been loaded yet.
	 *
	 * @return array[] Each element is an info array from get_treatment_info().
	 */
	public static function get_pending(): array {
		// Ensure all treatment files have been loaded (and their boot() called).
		if ( empty( self::$treatments ) ) {
			if ( class_exists( '\\WPShadow\\Treatments\\Treatment_Registry' ) ) {
				\WPShadow\Treatments\Treatment_Registry::get_all();
			}
		}

		$pending = [];

		foreach ( self::$treatments as $class ) {
			if ( ! class_exists( $class ) ) {
				continue;
			}

			$finding_id = method_exists( $class, 'get_finding_id' )
				? $class::get_finding_id()
				: '';

			if ( empty( $finding_id ) ) {
				continue;
			}

			// Check whether this finding is currently active.
			if ( ! self::is_finding_active( $finding_id ) ) {
				continue;
			}

			$info = self::get_treatment_info( $class );
			if ( $info ) {
				$pending[] = $info;
			}
		}

		return $pending;
	}

	/**
	 * Get structured info array for a single treatment class.
	 *
	 * @param string $class Fully-qualified class name.
	 * @return array|null Info array or null if class is invalid.
	 */
	public static function get_treatment_info( string $class ): ?array {
		if ( ! class_exists( $class ) ) {
			return null;
		}

		$required_methods = [
			'get_finding_id',
			'get_target_file',
			'get_file_label',
			'get_proposed_change_summary',
			'get_proposed_snippet',
			'get_sftp_undo_instructions',
		];

		foreach ( $required_methods as $method ) {
			if ( ! method_exists( $class, $method ) ) {
				return null;
			}
		}

		return [
			'class'            => $class,
			'finding_id'       => $class::get_finding_id(),
			'target_file'      => $class::get_target_file(),
			'file_label'       => $class::get_file_label(),
			'change_summary'   => $class::get_proposed_change_summary(),
			'snippet'          => $class::get_proposed_snippet(),
			'sftp_instructions'=> $class::get_sftp_undo_instructions(),
			'risk_level'       => method_exists( $class, 'get_risk_level' ) ? $class::get_risk_level() : 'high',
		];
	}

	/**
	 * Check whether a finding is currently active (failing diagnostic).
	 *
	 * @param string $finding_id Diagnostic finding ID slug.
	 * @return bool True if the finding exists in the current scan results.
	 */
	private static function is_finding_active( string $finding_id ): bool {
		// Attempt to use the findings cache if available.
		if ( function_exists( 'wpshadow_get_findings_cache' ) ) {
			$findings = wpshadow_get_findings_cache();
			if ( is_array( $findings ) ) {
				foreach ( $findings as $finding ) {
					if ( isset( $finding['finding_id'] ) && $finding['finding_id'] === $finding_id ) {
						return true;
					}
					// Also check a plain 'id' key used by some finding formats.
					if ( isset( $finding['id'] ) && $finding['id'] === $finding_id ) {
						return true;
					}
				}
				return false;
			}
		}

		// Fallback: treat as active so we never silently skip a real issue.
		return true;
	}
}
