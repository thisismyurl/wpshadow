<?php
/**
 * Ultimate Member Email Queue Diagnostic
 *
 * Ultimate Member emails queueing poorly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.523.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ultimate Member Email Queue Diagnostic Class
 *
 * @since 1.523.0000
 */
class Diagnostic_UltimateMemberEmailQueue extends Diagnostic_Base {

	protected static $slug = 'ultimate-member-email-queue';
	protected static $title = 'Ultimate Member Email Queue';
	protected static $description = 'Ultimate Member emails queueing poorly';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ultimatemember_version' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/ultimate-member-email-queue',
			);
		}
		
		return null;
	}
}
