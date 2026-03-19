<?php
/**
 * Git Version Control Diagnostic
 *
 * Checks if version control (Git) is properly configured.
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
 * Git Version Control Diagnostic Class
 *
 * Verifies that Git version control is properly configured with
 * appropriate .gitignore and repository structure.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Git_Version_Control extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'git-version-control';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Git Version Control';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if version control (Git) is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the git version control diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if version control issues detected, null otherwise.
	 */
	public static function check() {
		$issues   = array();
		$warnings = array();
		$config   = array();

		// Check if .git directory exists.
		$git_dir           = ABSPATH . '.git';
		$config['has_git'] = is_dir( $git_dir );

		if ( ! $config['has_git'] ) {
			// Check parent directories (theme/plugin might be in git).
			$parent_git = dirname( ABSPATH ) . '/.git';
			if ( is_dir( $parent_git ) ) {
				$config['has_git']      = true;
				$config['git_location'] = 'parent';
			} else {
				$issues[] = __( 'Git repository not detected - version control not initialized', 'wpshadow' );
			}
		} else {
			$config['git_location'] = 'root';
		}

		if ( $config['has_git'] ) {

			// Check for .gitignore.
			$gitignore_file          = ABSPATH . '.gitignore';
			$config['has_gitignore'] = file_exists( $gitignore_file );

			if ( ! $config['has_gitignore'] ) {
				$issues[] = __( '.gitignore file missing - sensitive files may be committed', 'wpshadow' );
			} else {
				// Validate .gitignore content.
				$gitignore_content = file_get_contents( $gitignore_file );

				// Critical files/directories that should be ignored.
				$should_ignore = array(
					'wp-config.php' => 'wp-config.php',
					'.env'          => '.env',
					'debug.log'     => 'debug.log',
					'uploads'       => 'wp-content/uploads',
					'cache'         => 'wp-content/cache',
					'node_modules'  => 'node_modules',
				);

				$missing_ignores = array();
				foreach ( $should_ignore as $key => $pattern ) {
					if ( strpos( $gitignore_content, $pattern ) === false &&
						strpos( $gitignore_content, $key ) === false ) {
						$missing_ignores[] = $pattern;
					}
				}

				if ( ! empty( $missing_ignores ) ) {
					$warnings[] = sprintf(
						/* translators: %s: comma-separated list of missing patterns */
						__( '.gitignore missing patterns: %s', 'wpshadow' ),
						implode( ', ', $missing_ignores )
					);
				}

				// Check if wp-config.php is tracked (security issue).
				if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
					$wp_config_status = shell_exec( 'cd ' . escapeshellarg( ABSPATH ) . ' && git ls-files wp-config.php 2>&1' );
					if ( ! empty( $wp_config_status ) && strpos( $wp_config_status, 'wp-config.php' ) !== false ) {
						$issues[] = __( 'wp-config.php is tracked by Git - security risk', 'wpshadow' );
					}
				}

				// Check if .env is tracked.
				if ( file_exists( ABSPATH . '.env' ) ) {
					$env_status = shell_exec( 'cd ' . escapeshellarg( ABSPATH ) . ' && git ls-files .env 2>&1' );
					if ( ! empty( $env_status ) && strpos( $env_status, '.env' ) !== false ) {
						$issues[] = __( '.env file is tracked by Git - security risk', 'wpshadow' );
					}
				}
			}

			// Check for .git directory accessibility.
			$git_url  = home_url( '/.git/config' );
			$response = wp_remote_head(
				$git_url,
				array(
					'timeout'   => 5,
					'sslverify' => false,
				)
			);

			if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
				$issues[] = __( '.git directory is publicly accessible - security risk', 'wpshadow' );
			}

			// Check for README.md.
			$config['has_readme'] = file_exists( ABSPATH . 'README.md' );
			if ( ! $config['has_readme'] ) {
				$warnings[] = __( 'README.md missing - add project documentation', 'wpshadow' );
			}

			// Check for .gitattributes.
			$config['has_gitattributes'] = file_exists( ABSPATH . '.gitattributes' );
			if ( ! $config['has_gitattributes'] ) {
				$warnings[] = __( '.gitattributes missing - consider adding for consistent line endings', 'wpshadow' );
			}

			// Check if Git is configured (user.name, user.email).
			$git_user  = shell_exec( 'git config user.name 2>&1' );
			$git_email = shell_exec( 'git config user.email 2>&1' );

			if ( empty( $git_user ) || empty( $git_email ) ) {
				$warnings[] = __( 'Git user.name or user.email not configured', 'wpshadow' );
			}

			// Check for common Git hosting remotes.
			$git_remote           = shell_exec( 'cd ' . escapeshellarg( ABSPATH ) . ' && git remote -v 2>&1' );
			$config['has_remote'] = ! empty( $git_remote ) && strpos( $git_remote, 'origin' ) !== false;

			if ( ! $config['has_remote'] ) {
				$warnings[] = __( 'No Git remote configured - add origin for backup', 'wpshadow' );
			} else {
				// Detect hosting provider.
				$providers = array(
					'github.com'    => 'GitHub',
					'gitlab.com'    => 'GitLab',
					'bitbucket.org' => 'Bitbucket',
				);

				foreach ( $providers as $domain => $provider ) {
					if ( strpos( $git_remote, $domain ) !== false ) {
						$config['git_provider'] = $provider;
						break;
					}
				}
			}

			// Check for branch protection indicators.
			$has_branch_protection_file = file_exists( ABSPATH . '.github/branch_protection.yml' ) ||
											file_exists( ABSPATH . '.gitlab/push_rules.yml' );

			if ( ! $has_branch_protection_file ) {
				$warnings[] = __( 'No branch protection configuration detected', 'wpshadow' );
			}
		}

		// Check for alternative version control systems.
		if ( ! $config['has_git'] ) {
			$svn_dir = ABSPATH . '.svn';
			$hg_dir  = ABSPATH . '.hg';

			if ( is_dir( $svn_dir ) ) {
				$config['has_svn'] = true;
				$warnings[]        = __( 'SVN detected - consider migrating to Git', 'wpshadow' );
			} elseif ( is_dir( $hg_dir ) ) {
				$config['has_hg'] = true;
				$warnings[]       = __( 'Mercurial detected - consider migrating to Git', 'wpshadow' );
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Git version control has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/git-version-control',
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
				'description'  => __( 'Git version control has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/git-version-control',
				'context'      => array(
					'config'   => $config,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Git version control is properly configured.
	}
}
