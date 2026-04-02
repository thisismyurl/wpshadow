<?php
/**
 * Base Diagnostic Class
 *
 * All diagnostic checks should extend this class.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class for all diagnostic checks.
 */
abstract class Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = '';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '';

	/**
	 * The family this diagnostic belongs to (optional)
	 * Used to group related diagnostics together
	 *
	 * Example: 'asset-versions', 'head-cleanup', 'update-notifications'
	 *
	 * @var string
	 */
	protected static $family = '';

	/**
	 * Display name for the family (optional)
	 * Example: 'Asset Optimization', 'Head Cleanup Tasks'
	 *
	 * @var string
	 */
	protected static $family_label = '';


	/**
	 * Severity level of this diagnostic's finding.
	 *
	 * Drives dashboard colour coding, KPI bucketing, and notification priority.
	 * Values: 'critical' | 'high' | 'medium' | 'low'
	 *
	 *  string
	 */
	protected static $severity = 'medium';

	/**
	 * How often this diagnostic should be executed.
	 *
	 * Values:
	 *   'always'    - run on every scan (fast, stateless checks)
	 *   'on-change' - re-run after plugin/theme activation or update
	 *   'daily'     - at most once per day
	 *   'weekly'    - at most once per week
	 *   'monthly'   - at most once per month
	 *
	 *  string
	 */
	protected static $scan_frequency = 'daily';

	/**
	 * Whether this diagnostic should only run during off-peak hours.
	 *
	 * Set true for checks that run expensive DB queries or enumerate
	 * large tables, so they are deferred and do not impact live traffic.
	 *
	 *  bool
	 */
	protected static $off_hours = false;

	/**
	 * Estimated minutes to resolve this finding once detected.
	 *
	 * Used by the KPI tracker to calculate time-saved when a treatment
	 * is applied or the finding is marked resolved.
	 *
	 *  int
	 */
	protected static $time_to_fix_minutes = 15;

	/**
	 * One-line business impact statement displayed in the dashboard.
	 *
	 * Example: 'Unencrypted traffic exposes visitor data and harms SEO.'
	 *
	 *  string
	 */
	protected static $impact = '';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Returns an array of findings if issues found, null otherwise
	 */
	abstract public static function check();


	/**
	 * Execute diagnostic check with hooks.
	 *
	 * Wraps check() with before/after actions for extensibility.
	 *
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function execute() {
		$class = get_called_class();
		$slug  = static::get_slug();

		/**
		 * Fires before a diagnostic check is run.
		 *
		 * @param string $class Diagnostic class name.
		 * @param string $slug  Diagnostic slug/identifier.
		 */
		do_action( 'wpshadow_before_diagnostic_check', $class, $slug );

		$finding = static::check();

		// Log every diagnostic run to the activity log.
		if ( class_exists( Activity_Logger::class ) ) {
			$log_title = ! empty( static::$title ) ? static::$title : $slug;
			if ( null !== $finding ) {
				$log_details = sprintf(
					/* translators: %s: diagnostic title */
					__( 'Diagnostic found issue: %s', 'wpshadow' ),
					$log_title
				);
			} else {
				$log_details = sprintf(
					/* translators: %s: diagnostic title */
					__( 'Diagnostic passed: %s', 'wpshadow' ),
					$log_title
				);
			}
			Activity_Logger::log(
				'diagnostic_run',
				$log_details,
				static::get_family(),
				array(
					'class'       => $class,
					'slug'        => $slug,
					'has_finding' => null !== $finding,
				)
			);
		}

		/**
		 * Fires after a diagnostic check is run.
		 *
		 * @param string     $class   Diagnostic class name.
		 * @param string     $slug    Diagnostic slug/identifier.
		 * @param array|null $finding Finding result (null if no issues).
		 */
		do_action( 'wpshadow_after_diagnostic_check', $class, $slug, $finding );

		/**
		 * Filter diagnostic check result.
		 *
		 * Allows modification of diagnostic findings before they're stored/displayed.
		 *
		 * @param array|null $finding Finding result (null if no issues).
		 * @param string     $class   Diagnostic class name.
		 * @param string     $slug    Diagnostic slug/identifier.
		 */
		return apply_filters( 'wpshadow_diagnostic_result', $finding, $class, $slug );
	}

	/**
	 * Get the diagnostic slug
	 *
	 * @return string
	 */
	public static function get_slug(): string {
		return static::$slug;
	}

	/**
	 * Get the diagnostic title
	 *
	 * @return string
	 */
	public static function get_title(): string {
		return static::$title;
	}

	/**
	 * Get the diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return static::$description;
	}

	/**
	 * Check if the diagnostic is applicable
	 *
	 * @return bool
	 */
	public static function is_applicable(): bool {
		return true;
	}

	/**
	 * Get available treatments for this diagnostic
	 *
	 * @return array
	 */
	public static function get_available_treatments(): array {
		return array();
	}

	/**
	 * Get the family this diagnostic belongs to
	 *
	 * @return string
	 */
	public static function get_family(): string {
		return static::$family;
	}

	/**
	 * Get the family label/display name
	 *
	 * @return string
	 */
	public static function get_family_label(): string {
		return static::$family_label;
	}

	/**
	 * Check if this diagnostic belongs to a family
	 *
	 * @return bool
	 */
	public static function has_family(): bool {
		return ! empty( static::$family );
	}

	/**
	 * Check if this diagnostic can be grouped/fixed with others
	 *
	 * @return bool
	 */
	public static function is_family_fixable(): bool {
		return static::has_family();
	}

]633;E;{ printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get the severity level of this diagnostic.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * Falls back to Diagnostic_Metadata registry when not overridden on the class.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * @return string 'critical' | 'high' | 'medium' | 'low'\\n" "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_severity(): string {\\n' "$T"\x3b printf "%s%sif ( '' !== static::\\$severity ) {\\n" "$T" "$T"\x3b printf '%s%s%sreturn static::$severity\x3b\\n' "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sreturn (string) ( \\$meta['severity'] ?? 'medium' )\x3b\\n" "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get how often this diagnostic should run.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * Falls back to Diagnostic_Metadata registry when not overridden on the class.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * @return string 'always' | 'on-change' | 'daily' | 'weekly' | 'monthly'\\n" "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_scan_frequency(): string {\\n' "$T"\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sif ( ! empty( \\$meta['scan_frequency'] ) ) {\\n" "$T" "$T"\x3b printf "%s%s%sreturn (string) \\$meta['scan_frequency']\x3b\\n" "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf "%s%sreturn '' !== static::\\$scan_frequency ? static::\\$scan_frequency : 'daily'\x3b\\n" "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get whether this diagnostic should only run during off-peak hours.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * Falls back to Diagnostic_Metadata registry when not overridden on the class.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf '%s * @return bool\\n' "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_off_hours(): bool {\\n' "$T"\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sif ( isset( \\$meta['off_hours'] ) ) {\\n" "$T" "$T"\x3b printf "%s%s%sreturn (bool) \\$meta['off_hours']\x3b\\n" "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf '%s%sreturn static::$off_hours\x3b\\n' "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get the estimated minutes to resolve this finding.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * Falls back to Diagnostic_Metadata registry when not overridden on the class.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf '%s * @return int\\n' "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_time_to_fix_minutes(): int {\\n' "$T"\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sif ( isset( \\$meta['time_to_fix_minutes'] ) ) {\\n" "$T" "$T"\x3b printf "%s%s%sreturn (int) \\$meta['time_to_fix_minutes']\x3b\\n" "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf '%s%sreturn static::$time_to_fix_minutes\x3b\\n' "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get the one-line business impact statement for this diagnostic.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf "%s * Falls back to Diagnostic_Metadata registry when not overridden on the class.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf '%s * @return string\\n' "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_impact(): string {\\n' "$T"\x3b printf "%s%sif ( '' !== static::\\$impact ) {\\n" "$T" "$T"\x3b printf '%s%s%sreturn static::$impact\x3b\\n' "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sreturn (string) ( \\$meta['impact'] ?? '' )\x3b\\n" "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get the user-facing title, with Diagnostic_Metadata overrides applied.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf '%s * @return string\\n' "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_title(): string {\\n' "$T"\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sif ( ! empty( \\$meta['title'] ) ) {\\n" "$T" "$T"\x3b printf "%s%s%sreturn (string) \\$meta['title']\x3b\\n" "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf '%s%sreturn static::$title\x3b\\n' "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '\\n'\x3b printf '%s/**\\n' "$T"\x3b printf "%s * Get the user-facing description, with Diagnostic_Metadata overrides applied.\\n" "$T"\x3b printf '%s *\\n' "$T"\x3b printf '%s * @return string\\n' "$T"\x3b printf '%s */\\n' "$T"\x3b printf '%spublic static function get_description(): string {\\n' "$T"\x3b printf "%s%s\\$meta = Diagnostic_Metadata::get( static::\\$slug )\x3b\\n" "$T" "$T"\x3b printf "%s%sif ( ! empty( \\$meta['description'] ) ) {\\n" "$T" "$T"\x3b printf "%s%s%sreturn (string) \\$meta['description']\x3b\\n" "$T" "$T" "$T"\x3b printf '%s%s}\\n' "$T" "$T"\x3b printf '\\n'\x3b printf '%s%sreturn static::$description\x3b\\n' "$T" "$T"\x3b printf '%s}\\n' "$T"\x3b printf '}\\n'\x3b } >> /tmp/base_head.php;85d28b61-c3a2-40c5-a4c8-f3a8384ae871]633;C
	/**
	 * Get the severity level of this diagnostic.
	 *
	 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
	 *
	 * @return string 'critical' | 'high' | 'medium' | 'low'
	 */
	public static function get_severity(): string {
		if ( '' !== static::$severity ) {
			return static::$severity;
		}

		$meta = Diagnostic_Metadata::get( static::$slug );
		return (string) ( $meta['severity'] ?? 'medium' );
	}

	/**
	 * Get how often this diagnostic should run.
	 *
	 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
	 *
	 * @return string 'always' | 'on-change' | 'daily' | 'weekly' | 'monthly'
	 */
	public static function get_scan_frequency(): string {
		$meta = Diagnostic_Metadata::get( static::$slug );
		if ( ! empty( $meta['scan_frequency'] ) ) {
			return (string) $meta['scan_frequency'];
		}

		return '' !== static::$scan_frequency ? static::$scan_frequency : 'daily';
	}

	/**
	 * Get whether this diagnostic should only run during off-peak hours.
	 *
	 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
	 *
	 * @return bool
	 */
	public static function get_off_hours(): bool {
		$meta = Diagnostic_Metadata::get( static::$slug );
		if ( isset( $meta['off_hours'] ) ) {
			return (bool) $meta['off_hours'];
		}

		return static::$off_hours;
	}

	/**
	 * Get the estimated minutes to resolve this finding.
	 *
	 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
	 *
	 * @return int
	 */
	public static function get_time_to_fix_minutes(): int {
		$meta = Diagnostic_Metadata::get( static::$slug );
		if ( isset( $meta['time_to_fix_minutes'] ) ) {
			return (int) $meta['time_to_fix_minutes'];
		}

		return static::$time_to_fix_minutes;
	}

	/**
	 * Get the one-line business impact statement for this diagnostic.
	 *
	 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
	 *
	 * @return string
	 */
	public static function get_impact(): string {
		if ( '' !== static::$impact ) {
			return static::$impact;
		}

		$meta = Diagnostic_Metadata::get( static::$slug );
		return (string) ( $meta['impact'] ?? '' );
	}

	/**
	 * Get the user-facing title, with Diagnostic_Metadata overrides applied.
	 *
	 * @return string
	 */
	public static function get_title(): string {
		$meta = Diagnostic_Metadata::get( static::$slug );
		if ( ! empty( $meta['title'] ) ) {
			return (string) $meta['title'];
		}

		return static::$title;
	}

	/**
	 * Get the user-facing description, with Diagnostic_Metadata overrides applied.
	 *
	 * @return string
	 */
	public static function get_description(): string {
		$meta = Diagnostic_Metadata::get( static::$slug );
		if ( ! empty( $meta['description'] ) ) {
			return (string) $meta['description'];
		}

		return static::$description;
	}
}
