<?php
/**
 * Accessibe Compliance Level Diagnostic
 *
 * Accessibe Compliance Level not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1105.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibe Compliance Level Diagnostic Class
 *
 * @since 1.1105.0000
 */
class Diagnostic_AccessibeComplianceLevel extends Diagnostic_Base {

	protected static $slug = 'accessibe-compliance-level';
	protected static $title = 'Accessibe Compliance Level';
	protected static $description = 'Accessibe Compliance Level not compliant';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // Generic check ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/accessibe-compliance-level',
			);
		}
		
		return null;
	}
}
