<?php
declare(strict_types=1);
/**
 * PHP Version Diagnostic
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP version against requirements.
 */
class Diagnostic_PHP_Version extends Diagnostic_Base {

	protected static $slug        = 'php-version';
	protected static $title       = 'PHP Version Outdated';
	protected static $description = 'Your PHP version should be updated for better security and performance.';

	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$current_version     = PHP_VERSION;
		$recommended_version = '8.1';
		$minimum_version     = '7.4';

		// Critical if below minimum
		if ( version_compare( $current_version, $minimum_version, '<' ) ) {
			return array(
				'title'       => 'PHP Version Critically Outdated',
				'description' => sprintf(
					'PHP version %1$s is outdated and unsupported. Minimum required: %2$s. Update immediately for security.',
					$current_version,
					$minimum_version
				),
				'severity'    => 'high',
				'category'    => 'security',
			);
		}

		// Warning if below recommended
		if ( version_compare( $current_version, $recommended_version, '<' ) ) {
			return array(
				'title'       => self::$title,
				'description' => sprintf(
					'PHP version %1$s works but %2$s+ is recommended for better performance, security, and compatibility.',
					$current_version,
					$recommended_version
				),
				'severity'    => 'medium',
				'category'    => 'performance',
			);
		}

		return null;
	}
}
