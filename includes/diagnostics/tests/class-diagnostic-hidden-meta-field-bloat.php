<?php
/**
 * Hidden Meta Field Bloat Diagnostic
 *
 * Checks if there is excessive hidden meta data accumulation.
 *
 * @since   1.26033.0800
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Hidden_Meta_Field_Bloat Class
 *
 * Validates that hidden meta fields are not accumulating excessively.
 *
 * @since 1.26033.0800
 */
class Diagnostic_Hidden_Meta_Field_Bloat extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hidden-meta-field-bloat';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hidden Meta Field Bloat';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive accumulation of hidden meta fields';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'meta';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0800
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count hidden meta fields (those starting with underscore)
		$hidden_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_%'"
		);

		// Get total meta fields
		$total_meta_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta}"
		);

		// Check if hidden meta is more than 70% of total
		if ( $total_meta_count > 0 && ( $hidden_meta_count / $total_meta_count ) > 0.7 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: percentage of hidden meta fields */
					__( '%d%% of your post meta is hidden meta fields. This can impact performance. Consider cleaning up unused plugin data.', 'wpshadow' ),
					intval( ( $hidden_meta_count / $total_meta_count ) * 100 )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hidden-meta-field-bloat',
			);
		}

		return null; // Meta field accumulation is healthy
	}
}
