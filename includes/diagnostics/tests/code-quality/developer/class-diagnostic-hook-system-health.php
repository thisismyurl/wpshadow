<?php
/**
 * Hook System Health Diagnostic
 *
 * Checks if WordPress hooks are working properly without conflicts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hook System Health Diagnostic Class
 *
 * Verifies that WordPress hooks and action/filter system are working
 * properly without conflicts or errors.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Hook_System_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hook-system-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hook System Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress hooks are working properly without conflicts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the hook system health diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if hook issues detected, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		$issues   = array();
		$warnings = array();
		$stats    = array();

		if ( ! isset( $wp_filter ) || empty( $wp_filter ) ) {
			$warnings[] = __( 'No hooks registered - this is unusual', 'wpshadow' );
			return null;
		}

		// Count total hooks.
		$total_hooks = count( $wp_filter );
		$stats['total_hooks'] = $total_hooks;

		// Check for hooks with excessive callbacks.
		$excessive_callbacks = array();
		foreach ( $wp_filter as $hook_name => $hook_callbacks ) {
			if ( is_array( $hook_callbacks ) ) {
				$callback_count = 0;
				foreach ( $hook_callbacks as $priority => $callbacks ) {
					$callback_count += count( (array) $callbacks );
				}
				
				if ( $callback_count > 50 ) {
					$excessive_callbacks[ $hook_name ] = $callback_count;
				}
			}
		}

		if ( ! empty( $excessive_callbacks ) ) {
			$warnings[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d hooks with excessive callbacks (>50 each)', 'wpshadow' ),
				count( $excessive_callbacks )
			);
		}

		// Check for hooks with runtime errors in callbacks.
		$problematic_hooks = array();
		
		foreach ( $wp_filter as $hook_name => $hook_callbacks ) {
			if ( is_array( $hook_callbacks ) ) {
				foreach ( $hook_callbacks as $priority => $callbacks ) {
					foreach ( (array) $callbacks as $callback ) {
						if ( is_array( $callback ) && isset( $callback['function'] ) ) {
							$function = $callback['function'];
							
							// Check if callback is a string (function name).
							if ( is_string( $function ) && ! function_exists( $function ) ) {
								$problematic_hooks[] = sprintf(
									/* translators: 1: hook name, 2: function name */
									__( 'Hook "%1$s" has non-existent function: %2$s', 'wpshadow' ),
									$hook_name,
									$function
								);
							}
						}
					}
				}
			}
		}

		if ( ! empty( $problematic_hooks ) ) {
			$issues = array_merge( $issues, array_slice( $problematic_hooks, 0, 5 ) );
		}

		// Check critical WordPress hooks are registered.
		$critical_hooks = array(
			'init',
			'wp_loaded',
			'template_redirect',
			'wp_head',
			'wp_footer',
			'wp',
		);

		$missing_critical_hooks = array();
		foreach ( $critical_hooks as $hook ) {
			if ( ! isset( $wp_filter[ $hook ] ) || empty( $wp_filter[ $hook ] ) ) {
				$missing_critical_hooks[] = $hook;
			}
		}

		if ( ! empty( $missing_critical_hooks ) ) {
			$warnings[] = sprintf(
				/* translators: %s: hook names */
				__( 'Missing critical hooks: %s', 'wpshadow' ),
				implode( ', ', $missing_critical_hooks )
			);
		}

		// Check for duplicate hook registrations.
		$hook_callbacks_by_name = array();
		$duplicate_hooks = 0;

		foreach ( $wp_filter as $hook_name => $hook_callbacks ) {
			if ( is_array( $hook_callbacks ) ) {
				foreach ( $hook_callbacks as $priority => $callbacks ) {
					foreach ( (array) $callbacks as $callback_name => $callback_data ) {
						$key = serialize( $callback_data );
						$full_key = $hook_name . ':' . $key;
						
						if ( isset( $hook_callbacks_by_name[ $full_key ] ) ) {
							$duplicate_hooks++;
						} else {
							$hook_callbacks_by_name[ $full_key ] = true;
						}
					}
				}
			}
		}

		if ( $duplicate_hooks > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d potential duplicate hook registrations detected', 'wpshadow' ),
				$duplicate_hooks
			);
		}

		// Check for hooks with same priority (priority conflicts).
		$priority_conflicts = 0;
		foreach ( $wp_filter as $hook_name => $hook_callbacks ) {
			if ( is_array( $hook_callbacks ) ) {
				foreach ( $hook_callbacks as $priority => $callbacks ) {
					if ( is_array( $callbacks ) && count( $callbacks ) > 20 ) {
						$priority_conflicts++;
					}
				}
			}
		}

		if ( $priority_conflicts > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of conflicts */
				__( '%d hooks with priority conflicts (many callbacks at same priority)', 'wpshadow' ),
				$priority_conflicts
			);
		}

		// Check plugin hook registrations.
		$plugins_with_hooks = array();
		$plugin_hook_count = array();

		foreach ( $wp_filter as $hook_callbacks ) {
			if ( is_array( $hook_callbacks ) ) {
				foreach ( $hook_callbacks as $priority => $callbacks ) {
					foreach ( (array) $callbacks as $callback_data ) {
						// Try to identify plugin from callback.
						if ( is_array( $callback_data ) && isset( $callback_data['function'] ) ) {
							$function = $callback_data['function'];
							
							// Class method.
							if ( is_array( $function ) && isset( $function[0] ) ) {
								$class_name = get_class( $function[0] );
								if ( strpos( $class_name, 'WPShadow' ) === false ) {
									if ( ! isset( $plugin_hook_count[ $class_name ] ) ) {
										$plugin_hook_count[ $class_name ] = 0;
									}
									$plugin_hook_count[ $class_name ]++;
								}
							}
						}
					}
				}
			}
		}

		$stats['plugin_hooks'] = count( $plugin_hook_count );
		$stats['total_hooks_registered'] = $total_hooks;

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Hook system has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hook-system-health',
				'context'      => array(
					'stats'                  => $stats,
					'excessive_callbacks'    => array_slice( $excessive_callbacks, 0, 5 ),
					'missing_critical_hooks' => $missing_critical_hooks,
					'issues'                 => $issues,
					'warnings'               => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Hook system has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hook-system-health',
				'context'      => array(
					'stats'                  => $stats,
					'excessive_callbacks'    => array_slice( $excessive_callbacks, 0, 5 ),
					'warnings'               => $warnings,
				),
			);
		}

		return null; // Hook system is healthy.
	}
}
