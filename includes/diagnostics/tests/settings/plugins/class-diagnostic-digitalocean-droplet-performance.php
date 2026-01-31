<?php
/**
 * Digitalocean Droplet Performance Diagnostic
 *
 * Digitalocean Droplet Performance needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1017.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Digitalocean Droplet Performance Diagnostic Class
 *
 * @since 1.1017.0000
 */
class Diagnostic_DigitaloceanDropletPerformance extends Diagnostic_Base {

	protected static $slug = 'digitalocean-droplet-performance';
	protected static $title = 'Digitalocean Droplet Performance';
	protected static $description = 'Digitalocean Droplet Performance needs attention';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/digitalocean-droplet-performance',
			);
		}
		
		return null;
	}
}
