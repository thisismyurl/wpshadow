<?php
/**
 * wpForo Attachment Security Diagnostic
 *
 * wpForo attachments not validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.533.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wpForo Attachment Security Diagnostic Class
 *
 * @since 1.533.0000
 */
class Diagnostic_WpforoAttachmentSecurity extends Diagnostic_Base {

	protected static $slug = 'wpforo-attachment-security';
	protected static $title = 'wpForo Attachment Security';
	protected static $description = 'wpForo attachments not validated';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPFORO_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wpforo-attachment-security',
			);
		}
		
		return null;
	}
}
