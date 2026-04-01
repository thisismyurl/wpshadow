<?php
/**
 * Media Hotlinking Protection Diagnostic
 *
 * Checks for hotlinking protection rules in the uploads
 * directory and warns if missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Hotlinking_Protection Class
 *
 * Detects whether hotlinking protection is configured.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Media_Hotlinking_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-hotlinking-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hotlinking Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hotlinking protection is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$upload_dir = wp_upload_dir();
		$htaccess = trailingslashit( $upload_dir['basedir'] ) . '.htaccess';

		if ( file_exists( $htaccess ) ) {
			$contents = file_get_contents( $htaccess );
			if ( false === $contents || ! preg_match( '/HTTP_REFERER/i', $contents ) ) {
				$issues[] = __( 'Uploads .htaccess does not contain hotlinking rules; consider enabling referrer protection', 'wpshadow' );
			}
		} else {
			$issues[] = __( 'Uploads .htaccess file not found; hotlinking protection may be missing', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-hotlinking-protection?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
