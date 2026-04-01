<?php
/**
 * Plugin Data Not Erased in GDPR Request Diagnostic
 *
 * Detects when third-party plugins fail to delete user data during erasure requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Data_Not_Erased_In_GDPR_Request Class
 *
 * Verifies that plugins properly delete user data during GDPR erasure.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Plugin_Data_Not_Erased_In_GDPR_Request extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-data-not-erased-in-gdpr-request';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin GDPR Erasure Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if active plugins register data erasers for GDPR compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all registered erasers.
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );

		// Map plugins that commonly store user data.
		$data_heavy_plugins = array(
			'woocommerce/woocommerce.php'                               => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php'         => 'Easy Digital Downloads',
			'buddypress/bp-loader.php'                                  => 'BuddyPress',
			'bbpress/bbpress.php'                                       => 'bbPress',
			'memberpress/memberpress.php'                               => 'MemberPress',
			'wp-user-frontend/wpuf.php'                                 => 'WP User Frontend',
			'ultimate-member/ultimate-member.php'                       => 'Ultimate Member',
			'gravity-forms/gravityforms.php'                            => 'Gravity Forms',
			'contact-form-7/wp-contact-form-7.php'                      => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'                               => 'Ninja Forms',
			'wpforms/wpforms.php'                                       => 'WPForms',
			'jetpack/jetpack.php'                                       => 'Jetpack',
			'learndash/learndash.php'                                   => 'LearnDash',
			'lifterlms/lifterlms.php'                                   => 'LifterLMS',
			'tutor/tutor.php'                                           => 'Tutor LMS',
			'wp-job-manager/wp-job-manager.php'                         => 'WP Job Manager',
			'profilepress/profilepress.php'                             => 'ProfilePress',
			'paid-memberships-pro/paid-memberships-pro.php'             => 'Paid Memberships Pro',
			'restrict-content-pro/restrict-content-pro.php'             => 'Restrict Content Pro',
			'wpml/sitepress.php'                                        => 'WPML',
		);

		$issues         = array();
		$plugins_found  = array();
		$missing_erasers = array();

		// Check each plugin.
		foreach ( $data_heavy_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$plugins_found[] = $plugin_name;

				// Check if plugin has registered an eraser.
				$has_eraser = false;
				foreach ( $erasers as $eraser_id => $eraser ) {
					if ( isset( $eraser['eraser_friendly_name'] ) ) {
						$eraser_name = strtolower( $eraser['eraser_friendly_name'] );
						$plugin_key  = strtolower( str_replace( ' ', '', $plugin_name ) );

						if ( false !== strpos( $eraser_name, $plugin_key ) ||
						     false !== strpos( $eraser_id, $plugin_key ) ) {
							$has_eraser = true;
							break;
						}
					}
				}

				if ( ! $has_eraser ) {
					$missing_erasers[] = $plugin_name;
				}
			}
		}

		if ( ! empty( $missing_erasers ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of plugins */
				__( 'Plugins without GDPR erasers: %s', 'wpshadow' ),
				implode( ', ', $missing_erasers )
			);
		}

		// Check for custom post types with user data.
		global $wpdb;
		$custom_post_types = get_post_types( array( '_builtin' => false ), 'objects' );

		foreach ( $custom_post_types as $post_type ) {
			// Check if post type has user-associated data.
			$post_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_author > 0",
					$post_type->name
				)
			);

			if ( (int) $post_count > 0 ) {
				// Check if there's an eraser for this post type.
				$has_cpt_eraser = false;
				foreach ( $erasers as $eraser ) {
					if ( isset( $eraser['eraser_friendly_name'] ) &&
					     false !== strpos( strtolower( $eraser['eraser_friendly_name'] ), strtolower( $post_type->name ) ) ) {
						$has_cpt_eraser = true;
						break;
					}
				}

				if ( ! $has_cpt_eraser ) {
					$issues[] = sprintf(
						/* translators: %s: post type label */
						__( 'Custom post type "%s" has no registered eraser', 'wpshadow' ),
						$post_type->label
					);
				}
			}
		}

		// Check for custom database tables.
		$custom_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );
		$core_tables   = array(
			$wpdb->posts,
			$wpdb->postmeta,
			$wpdb->comments,
			$wpdb->commentmeta,
			$wpdb->users,
			$wpdb->usermeta,
			$wpdb->links,
			$wpdb->options,
			$wpdb->termmeta,
			$wpdb->terms,
			$wpdb->term_relationships,
			$wpdb->term_taxonomy,
		);

		$plugin_tables = array_diff( $custom_tables, $core_tables );

		if ( ! empty( $plugin_tables ) ) {
			// Check if any tables have user_id columns.
			$tables_with_user_data = array();
			foreach ( $plugin_tables as $table ) {
				$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$table}" );
				if ( in_array( 'user_id', $columns, true ) || in_array( 'author_id', $columns, true ) ) {
					$tables_with_user_data[] = $table;
				}
			}

			if ( ! empty( $tables_with_user_data ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of tables */
					_n(
						'%d custom table with user data found - verify erasure coverage',
						'%d custom tables with user data found - verify erasure coverage',
						count( $tables_with_user_data ),
						'wpshadow'
					),
					count( $tables_with_user_data )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Plugin GDPR erasure gaps detected: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 90,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/plugin-gdpr-erasure?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'           => $issues,
				'plugins_checked'  => $plugins_found,
				'missing_erasers'  => $missing_erasers,
				'eraser_count'     => count( $erasers ),
			),
		);
	}
}
