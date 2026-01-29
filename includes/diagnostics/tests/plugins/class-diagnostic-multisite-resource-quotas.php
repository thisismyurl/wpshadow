<?php
/**
 * Multisite Resource Quotas Diagnostic
 *
 * Multisite Resource Quotas misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.988.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Resource Quotas Diagnostic Class
 *
 * @since 1.988.0000
 */
class Diagnostic_MultisiteResourceQuotas extends Diagnostic_Base {

	protected static $slug = 'multisite-resource-quotas';
	protected static $title = 'Multisite Resource Quotas';
	protected static $description = 'Multisite Resource Quotas misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/multisite-resource-quotas',
			);
		}
		
		return null;
	}
}
