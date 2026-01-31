<?php
/**
 * Comment Spam Detection Not Enhanced Diagnostic
 *
 * Checks if comment spam detection is enhanced.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2347
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Detection Not Enhanced Diagnostic Class
 *
 * Detects missing enhanced spam detection.
 *
 * @since 1.2601.2347
 */
class Diagnostic_Comment_Spam_Detection_Not_Enhanced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-detection-not-enhanced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Detection Not Enhanced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment spam detection is enhanced';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2347
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for spam detection plugins
		$spam_plugins = array(
			'akismet/akismet.php',
			'antispam-bee/antispam-bee.php',
			'wp-spamshield/wp-spamshield.php',
		);

		$spam_active = false;
		foreach ( $spam_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$spam_active = true;
				break;
			}
		}

		if ( ! $spam_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment spam detection is not enhanced with specialized plugins. Use Akismet or similar for better spam protection.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-spam-detection-not-enhanced',
			);
		}

		return null;
	}
}
