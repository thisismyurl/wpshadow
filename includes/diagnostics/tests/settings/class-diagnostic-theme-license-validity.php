<?php
/**
 * Theme License Validity Diagnostic
 *
 * Validates that installed themes have valid, properly declared licenses
 * and that commercial themes are legitimately licensed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme License Validity Diagnostic Class
 *
 * Checks theme license validity and compliance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_License_Validity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-license-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme License Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme license validity and compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get all themes.
		$themes = wp_get_themes();

		// Validate theme licenses.
		$license_issues = array();

		foreach ( $themes as $theme_slug => $theme ) {
			$theme_name   = $theme->get( 'Name' );
			$license      = $theme->get( 'License' );
			$license_uri  = $theme->get( 'LicenseURI' );
			$theme_uri    = $theme->get( 'ThemeURI' );
			$parent_theme = $theme->get_template();

			// Check for missing license info.
			if ( empty( $license ) && empty( $license_uri ) ) {
				$license_issues[] = array(
					'theme'  => $theme_name,
					'issue'  => 'No license information declared',
				);
			}

			// Validate license string.
			if ( ! empty( $license ) ) {
				$valid_licenses = array(
					'GPL v2 or later',
					'GPL v3 or later',
					'GPL2',
					'GPL3',
					'MIT',
					'Apache 2.0',
					'BSD',
					'CC0.6093.1200 Universal',
				);

				$license_valid = false;
				foreach ( $valid_licenses as $valid_lic ) {
					if ( false !== stripos( $license, $valid_lic ) ) {
						$license_valid = true;
						break;
					}
				}

				if ( ! $license_valid && strlen( $license ) < 5 ) {
					$license_issues[] = array(
						'theme'  => $theme_name,
						'issue'  => 'Non-standard or unclear license: ' . $license,
					);
				}
			}

			// Check license URI validity.
			if ( ! empty( $license_uri ) ) {
				if ( ! filter_var( $license_uri, FILTER_VALIDATE_URL ) ) {
					$license_issues[] = array(
						'theme'  => $theme_name,
						'issue'  => 'License URI is not a valid URL',
					);
				}
			}

			// Check for commercial themes without license info.
			if ( ! empty( $theme_uri ) ) {
				// Check if theme is from commercial marketplace.
				$is_commercial = false;

				if ( false !== stripos( $theme_uri, 'themeforest' ) ) {
					$is_commercial = true;
				}

				if ( false !== stripos( $theme_uri, 'templatemonster' ) ) {
					$is_commercial = true;
				}

				if ( false !== stripos( $theme_uri, 'elegant' ) ) {
					$is_commercial = true;
				}

				if ( $is_commercial && empty( $license_uri ) ) {
					$license_issues[] = array(
						'theme'  => $theme_name,
						'issue'  => 'Commercial theme without license URI (verify purchase status)',
					);
				}
			}
		}

		// Check for GPL compliance in child themes.
		$stylesheet = wp_get_theme()->get_stylesheet();
		$template   = wp_get_theme()->get_template();

		if ( $stylesheet !== $template ) {
			// This is a child theme.
			$parent_theme = wp_get_theme( $template );

			if ( empty( $parent_theme ) ) {
				$issues[] = __( 'Child theme parent theme is missing', 'wpshadow' );
			} else {
				$parent_license = $parent_theme->get( 'License' );

				if ( empty( $parent_license ) ) {
					$issues[] = __( 'Parent theme license information missing', 'wpshadow' );
				}
			}
		}

		// Check for license key storage (for commercial themes).
		global $wpdb;

		// Search for license keys in options.
		$license_keys = $wpdb->get_results(
			"SELECT option_name, option_value
			FROM {$wpdb->options}
			WHERE option_name LIKE '%license%'
			OR option_name LIKE '%key%'
			LIMIT 20"
		);

		$insecure_storage = array();
		foreach ( $license_keys as $option ) {
			// Check if license key is stored in plain text.
			if ( ! empty( $option->option_value ) && strlen( $option->option_value ) > 20 && strlen( $option->option_value ) < 100 ) {
				// Could be a license key - check if it's encrypted.
				if ( false === strpos( $option->option_value, 'serialized' ) ) {
					// Not serialized - might be plain text.
					$insecure_storage[] = $option->option_name;
				}
			}
		}

		if ( ! empty( $insecure_storage ) ) {
			$issues[] = sprintf(
				/* translators: %s: option names */
				__( 'Possible unencrypted license keys stored in: %s', 'wpshadow' ),
				implode( ', ', $insecure_storage )
			);
		}

		// Add license issues to main issues array.
		foreach ( $license_issues as $issue ) {
			$issues[] = sprintf(
				'%s: %s',
				$issue['theme'],
				$issue['issue']
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of license issues */
					__( 'Found %d theme license validity issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'total_themes'      => count( $themes ),
					'themes_with_issues' => count( $license_issues ),
					'recommendation'    => __( 'Declare valid licenses for all themes. Verify commercial theme purchases. Store license keys securely (encrypted or not in database).', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
