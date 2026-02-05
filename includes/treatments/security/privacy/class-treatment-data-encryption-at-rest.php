<?php
/**
 * Data Encryption At Rest Treatment
 *
 * Checks whether sensitive data is protected with encryption at rest.
 *
 * @package    WPShadow
 * @subpackage Treatments\Privacy
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Encryption At Rest Treatment Class
 *
 * Verifies that encryption tools are present for sensitive data.
 *
 * @since 1.6035.1400
 */
class Treatment_Data_Encryption_At_Rest extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'data-encryption-at-rest';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'User Data Not Encrypted At Rest';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if encryption tools protect sensitive data at rest';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$encryption_plugins = array(
			'wpdataencrypt/wpdataencrypt.php' => 'WP Data Encrypt',
			'wp-encrypt/wp-encrypt.php'       => 'WP Encrypt',
			'defender-security/wp-defender.php' => 'Defender Security',
		);

		$active_encryption = array();
		foreach ( $encryption_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_encryption[] = $plugin_name;
			}
		}

		$stats['encryption_tools'] = ! empty( $active_encryption ) ? implode( ', ', $active_encryption ) : 'none';
		$stats['sodium_available'] = extension_loaded( 'sodium' ) ? 'yes' : 'no';

		if ( empty( $active_encryption ) ) {
			$issues[] = __( 'No encryption-at-rest tools detected for sensitive data', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Encrypting sensitive data at rest protects people if a database is ever copied or leaked. It is like keeping valuables in a locked safe instead of a drawer.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/data-encryption-at-rest',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
