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
}
