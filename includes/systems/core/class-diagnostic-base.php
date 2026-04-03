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
 * The diagnostic slug/ID.
 *
 * @var string
 */
protected static $slug = '';

/**
 * The diagnostic title.
 *
 * @var string
 */
protected static $title = '';

/**
 * The diagnostic description.
 *
 * @var string
 */
protected static $description = '';

/**
 * The family this diagnostic belongs to.
 *
 * Used to group related diagnostics on the dashboard.
 * Example: 'performance', 'security', 'seo'
 *
 * @var string
 */
protected static $family = '';

/**
 * Display name for the family.
 *
 * Example: 'Performance', 'Security'
 *
 * @var string
 */
protected static $family_label = '';

/**
 * Severity level of this diagnostic finding.
 *
 * Drives dashboard colour coding, KPI bucketing, and notification priority.
 * Values: 'critical' | 'high' | 'medium' | 'low'
 *
 * @var string
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
 * @var string
 */
protected static $scan_frequency = 'daily';

/**
 * Whether this diagnostic should only run during off-peak hours.
 *
 * Set true for checks that execute expensive DB queries or enumerate
 * large tables, so they are deferred and do not impact live traffic.
 *
 * @var bool
 */
protected static $off_hours = false;

/**
 * Estimated minutes to resolve this finding once detected.
 *
 * Used by the KPI tracker to calculate time-saved metrics when a
 * treatment is applied or the finding is marked resolved.
 *
 * @var int
 */
protected static $time_to_fix_minutes = 15;

/**
 * One-line business impact statement displayed in the dashboard.
 *
 * Example: 'Unencrypted traffic exposes visitor data and harms SEO.'
 *
 * @var string
 */
protected static $impact = '';

/**
 * Whether this diagnostic is part of the core trusted set.
 *
 * Core diagnostics are universally applicable, high-confidence, and high-impact.
 * They are recommended for all sites and designed to minimize false positives.
 *
 * @var bool
 */
protected static $is_core = false;

/**
 * Confidence level of this diagnostic.
 *
 * Values:
 *   'high'     - Well-tested, universally applicable, low false-positive rate
 *   'standard' - Active, implemented, works in most scenarios
 *   'low'      - Beta/experimental, context-dependent, or not yet fully validated
 *
 * Defaults to 'standard'. Core diagnostics should be 'high' or 'standard'.
 *
 * @var string
 */
protected static $confidence = 'standard';

/**
 * Run the diagnostic check.
 *
 * @return array|null Returns an array of findings if issues found, null otherwise.
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
 * Allows modification of diagnostic findings before stored/displayed.
 *
 * @param array|null $finding Finding result (null if no issues).
 * @param string     $class   Diagnostic class name.
 * @param string     $slug    Diagnostic slug/identifier.
 */
return apply_filters( 'wpshadow_diagnostic_result', $finding, $class, $slug );
}

/**
 * Get the diagnostic slug.
 *
 * @return string
 */
public static function get_slug(): string {
return static::$slug;
}

/**
 * Get the user-facing title, merging Diagnostic_Metadata overrides.
 *
 * @return string
 */
public static function get_title(): string {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['title'] ) ) {
return (string) $meta['title'];
}
}

return static::$title;
}

/**
 * Get the user-facing description, merging Diagnostic_Metadata overrides.
 *
 * @return string
 */
public static function get_description(): string {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['description'] ) ) {
return (string) $meta['description'];
}
}

return static::$description;
}

/**
 * Get the severity level of this diagnostic.
 *
 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
 *
 * @return string 'critical' | 'high' | 'medium' | 'low'
 */
public static function get_severity(): string {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['severity'] ) ) {
return (string) $meta['severity'];
}
}

return '' !== static::$severity ? static::$severity : 'medium';
}

/**
 * Get how often this diagnostic should run.
 *
 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
 *
 * @return string 'always' | 'on-change' | 'daily' | 'weekly' | 'monthly'
 */
public static function get_scan_frequency(): string {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['scan_frequency'] ) ) {
return (string) $meta['scan_frequency'];
}
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
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( isset( $meta['off_hours'] ) ) {
return (bool) $meta['off_hours'];
}
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
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( isset( $meta['time_to_fix_minutes'] ) ) {
return (int) $meta['time_to_fix_minutes'];
}
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
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['impact'] ) ) {
return (string) $meta['impact'];
}
}

return static::$impact;
}

/**
 * Check if this diagnostic is part of the core trusted set.
 *
 * Core diagnostics are universally applicable and high-confidence.
 *
 * @return bool
 */
public static function is_core(): bool {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( isset( $meta['is_core'] ) ) {
return (bool) $meta['is_core'];
}
}

return static::$is_core;
}

/**
 * Get the confidence level of this diagnostic.
 *
 * Falls back to Diagnostic_Metadata registry when not overridden on the class.
 *
 * @return string 'high' | 'standard' | 'low'
 */
public static function get_confidence(): string {
if ( class_exists( Diagnostic_Metadata::class ) ) {
$meta = Diagnostic_Metadata::get( static::$slug );
if ( ! empty( $meta['confidence'] ) ) {
return (string) $meta['confidence'];
}
}

return '' !== static::$confidence ? static::$confidence : 'standard';
}

/**
 * Check if the diagnostic is applicable.
 *
 * @return bool
 */
public static function is_applicable(): bool {
return true;
}

/**
 * Get available treatments for this diagnostic.
 *
 * @return array
 */
public static function get_available_treatments(): array {
return array();
}

/**
 * Get the family this diagnostic belongs to.
 *
 * @return string
 */
public static function get_family(): string {
return static::$family;
}

/**
 * Get the family label/display name.
 *
 * @return string
 */
public static function get_family_label(): string {
return static::$family_label;
}

/**
 * Check if this diagnostic belongs to a family.
 *
 * @return bool
 */
public static function has_family(): bool {
return ! empty( static::$family );
}

/**
 * Check if this diagnostic can be grouped/fixed with others in its family.
 *
 * @return bool
 */
public static function is_family_fixable(): bool {
return static::has_family();
}
}
