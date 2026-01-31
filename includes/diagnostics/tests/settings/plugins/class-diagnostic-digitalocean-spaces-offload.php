<?php
/**
 * Digitalocean Spaces Offload Diagnostic
 *
 * Digitalocean Spaces Offload needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1015.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digitalocean Spaces Offload Diagnostic Class
 *
 * @since 1.1015.0000
 */
class Diagnostic_DigitaloceanSpacesOffload extends Diagnostic_Base {

	protected static $slug = 'digitalocean-spaces-offload';
	protected static $title = 'Digitalocean Spaces Offload';
	protected static $description = 'Digitalocean Spaces Offload needs attention';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/digitalocean-spaces-offload',
			);
		}
		
		return null;
	}
}
