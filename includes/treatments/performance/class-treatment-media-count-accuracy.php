<?php
/**
 * Media Count Accuracy Treatment
 *
 * Verifies media counts shown in the UI match database counts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Count_Accuracy Class
 *
 * Checks attachment counts returned by WordPress APIs
 * against direct database totals.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_Count_Accuracy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-count-accuracy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Count Accuracy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media counts match database totals';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		$issues = array();

		$wp_counts = wp_count_posts( 'attachment' );
		$wp_total = 0;
		foreach ( (array) $wp_counts as $status => $count ) {
			if ( is_numeric( $count ) ) {
				$wp_total += (int) $count;
			}
		}

		$db_total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);

		if ( $wp_total !== $db_total ) {
			$issues[] = sprintf(
				/* translators: 1: WordPress count, 2: database count */
				__( 'Media count mismatch: WordPress reports %1$s items while database has %2$s attachments', 'wpshadow' ),
				number_format_i18n( $wp_total ),
				number_format_i18n( $db_total )
			);
		}

		$inherit_count = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_status = 'inherit'"
		);
		if ( isset( $wp_counts->inherit ) && (int) $wp_counts->inherit !== $inherit_count ) {
			$issues[] = sprintf(
				/* translators: 1: WordPress inherit count, 2: database inherit count */
				__( 'Attachment inherit count mismatch: WordPress shows %1$s but database has %2$s', 'wpshadow' ),
				number_format_i18n( (int) $wp_counts->inherit ),
				number_format_i18n( $inherit_count )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-count-accuracy',
			);
		}

		return null;
	}
}
