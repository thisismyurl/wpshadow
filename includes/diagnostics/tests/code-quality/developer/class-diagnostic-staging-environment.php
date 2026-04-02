<?php
/**
 * Staging Environment Diagnostic
 *
 * Checks if a staging/development environment is configured.
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
 * Staging Environment Diagnostic Class
 *
 * Verifies that a staging or development environment exists for testing
 * changes before deploying to production.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Staging_Environment extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'staging-environment';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Staging/Development Environment';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if a staging/development environment is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the staging environment diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if staging issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$config   = array();

		// Determine if this is production.
		$site_url = home_url();
		$is_local = in_array( $_SERVER['HTTP_HOST'] ?? '', array( 'localhost', '127.0.0.1', '::1' ), true ) ||
					strpos( $_SERVER['HTTP_HOST'] ?? '', '.local' ) !== false ||
					strpos( $_SERVER['HTTP_HOST'] ?? '', '.test' ) !== false ||
					strpos( $_SERVER['HTTP_HOST'] ?? '', '.dev' ) !== false;

		$is_staging = strpos( $_SERVER['HTTP_HOST'] ?? '', 'staging' ) !== false ||
						strpos( $_SERVER['HTTP_HOST'] ?? '', 'dev' ) !== false ||
						strpos( $_SERVER['HTTP_HOST'] ?? '', 'test' ) !== false;

		$config['is_local']      = $is_local;
		$config['is_staging']    = $is_staging;
		$config['is_production'] = ! $is_local && ! $is_staging;
		$config['site_url']      = $site_url;

		// Check for WP_ENVIRONMENT_TYPE constant (WP 5.5+).
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$environment_type           = wp_get_environment_type();
			$config['environment_type'] = $environment_type;
		} else {
			$config['environment_type'] = 'unknown';
		}

		// If this is production, check for indicators of staging setup.
		if ( $config['is_production'] ) {

			// Check for staging plugins.
			$staging_plugins = array(
				'wp-staging/wp-staging.php',
				'duplicator/duplicator.php',
				'all-in-one-wp-migration/all-in-one-wp-migration.php',
				'updraftplus/updraftplus.php',
			);

			$has_staging_plugin = false;
			foreach ( $staging_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					$has_staging_plugin       = true;
					$config['staging_plugin'] = dirname( $plugin );
					break;
				}
			}

			if ( ! $has_staging_plugin ) {
				$warnings[] = __( 'No staging plugin detected for creating test environments', 'wpshadow' );
			}

			// Check for managed hosting with staging (via constants or headers).
			$managed_hosts = array(
				'WPE_CLUSTER'          => 'WP Engine',
				'IS_WPE'               => 'WP Engine',
				'KINSTA_CACHE_ZONE'    => 'Kinsta',
				'FLYWHEEL_CONFIG_DIR'  => 'Flywheel',
				'PANTHEON_ENVIRONMENT' => 'Pantheon',
			);

			$managed_host = null;
			foreach ( $managed_hosts as $constant => $host_name ) {
				if ( defined( $constant ) ) {
					$managed_host = $host_name;
					break;
				}
			}

			$config['managed_host'] = $managed_host;

			if ( $managed_host ) {
				// Managed hosts typically provide staging.
				$config['has_managed_staging'] = true;
			} else {
				// Check for manual staging indicators.
				$has_staging_indicator = false;

				// Check for staging subdomain/subdirectory.
				$possible_staging_urls = array(
					str_replace( array( 'https://', 'http://' ), array( 'https://staging.', 'http://staging.' ), $site_url ),
					str_replace( array( 'https://', 'http://' ), array( 'https://dev.', 'http://dev.' ), $site_url ),
					$site_url . '/staging',
					$site_url . '/dev',
				);

				foreach ( $possible_staging_urls as $staging_url ) {
					$response = wp_remote_head(
						$staging_url,
						array(
							'timeout'   => 5,
							'sslverify' => false,
						)
					);

					if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
						$has_staging_indicator          = true;
						$config['detected_staging_url'] = $staging_url;
						break;
					}
				}

				if ( ! $has_staging_indicator && ! $has_staging_plugin ) {
					$issues[] = __( 'No staging environment detected - risky to test changes on production', 'wpshadow' );
				}
			}
		} else {
			// This IS staging/local/dev.
			$config['current_environment'] = $is_local ? 'local' : 'staging';

			// Check if this staging has production URL configured.
			if ( defined( 'WP_HOME' ) && defined( 'WP_SITEURL' ) ) {
				$warnings[] = __( 'WP_HOME and WP_SITEURL hardcoded - good for staging', 'wpshadow' );
			}
		}

		// Check for environment config file.
		$env_file               = ABSPATH . '.env';
		$config['has_env_file'] = file_exists( $env_file );

		if ( $config['has_env_file'] ) {
			$warnings[] = __( '.env file detected - ensure it\'s not publicly accessible', 'wpshadow' );
		}

		// Check for deployment tools.
		$deployment_files = array(
			ABSPATH . 'deploy.php',
			ABSPATH . 'deploy.sh',
			ABSPATH . '.gitlab-ci.yml',
			ABSPATH . '.github/workflows',
			ABSPATH . 'bitbucket-pipelines.yml',
		);

		$has_deployment_tool = false;
		foreach ( $deployment_files as $file ) {
			if ( file_exists( $file ) ) {
				$has_deployment_tool       = true;
				$config['deployment_tool'] = basename( $file );
				break;
			}
		}

		if ( ! $has_deployment_tool ) {
			$warnings[] = __( 'No CI/CD deployment tool detected', 'wpshadow' );
		}

		// Check for robots.txt blocking (should be blocked on staging).
		if ( $config['is_staging'] ) {
			$robots_url = home_url( '/robots.txt' );
			$response   = wp_remote_get(
				$robots_url,
				array(
					'timeout'   => 5,
					'sslverify' => false,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				$robots_content = wp_remote_retrieve_body( $response );
				if ( strpos( $robots_content, 'Disallow: /' ) === false ) {
					$warnings[] = __( 'Staging site not blocking search engines in robots.txt', 'wpshadow' );
				}
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Staging environment setup has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/staging-environment',
				'context'      => array(
					'config'   => $config,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Staging environment has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/staging-environment',
				'context'      => array(
					'config'   => $config,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Staging environment is properly configured.
	}
}
