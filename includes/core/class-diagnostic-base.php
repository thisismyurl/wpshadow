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
	 * Run the diagnostic check
	 *
	 * @return array|null Returns an array of findings if issues found, null otherwise
	 */
	abstract public static function check();

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
}
