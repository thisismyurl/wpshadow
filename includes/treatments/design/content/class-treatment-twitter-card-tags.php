<?php
/**
 * Twitter Card Tags Treatment
 *
 * Issue #4975: No Twitter Card Tags
 * Pillar: #1: Helpful Neighbor
 *
 * Checks if Twitter Card tags are configured.
 * Twitter Cards control appearance on Twitter/X.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Twitter_Card_Tags Class
 *
 * @since 1.6050.0000
 */
class Treatment_Twitter_Card_Tags extends Treatment_Base {

	protected static $slug = 'twitter-card-tags';
	protected static $title = 'No Twitter Card Tags';
	protected static $description = 'Checks if Twitter Card meta tags are configured';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Twitter_Card_Tags' );
	}
}
