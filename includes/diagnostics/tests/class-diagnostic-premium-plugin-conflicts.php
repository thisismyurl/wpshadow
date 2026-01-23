<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Premium Plugin Compatibility
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Premium_Plugin_Conflicts extends Diagnostic_Base
{
	protected static $slug        = 'premium-plugin-conflicts';
	protected static $title       = 'Premium Plugin Compatibility';
	protected static $description = 'Detects conflicts with common premium plugins.';

	public static function check(): ?array
	{
		$conflict_pairs = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
			array('wordfence/wordfence.php', 'ithemes-security-pro/ithemes-security-pro.php'),
		);

		$conflicts_found = array();
		foreach ($conflict_pairs as $pair) {
			if (is_plugin_active($pair[0]) && is_plugin_active($pair[1])) {
				$conflicts_found[] = basename(dirname($pair[0])) . ' + ' . basename(dirname($pair[1]));
			}
		}

		if (empty($conflicts_found)) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Potential conflicts detected: ' . implode(', ', $conflicts_found),
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/premium-plugin-conflicts/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=premium-plugin-conflicts',
			'training_link' => 'https://wpshadow.com/training/premium-plugin-conflicts/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Compatibility',
			'priority'      => 2,
		);
	}

	/**
	 * Live test for this diagnostic
	 */
	public static function test_live_premium_plugin_conflicts(): array
	{
		$conflict_pairs = array(
			array('jetpack/jetpack.php', 'wp-rocket/wp-rocket.php'),
			array('wordfence/wordfence.php', 'ithemes-security-pro/ithemes-security-pro.php'),
		);

		$conflicts_found = array();
		foreach ($conflict_pairs as $pair) {
			if (is_plugin_active($pair[0]) && is_plugin_active($pair[1])) {
				$conflicts_found[] = basename(dirname($pair[0])) . ' + ' . basename(dirname($pair[1]));
			}
		}

		$expected_issue    = (count($conflicts_found) > 0);
		$diagnostic_result = self::check();
		$diagnostic_has_issue = (null !== $diagnostic_result);
		$test_passes          = ($expected_issue === $diagnostic_has_issue);

		$message = sprintf(
			'Conflict pairs active: %d (%s). Expected diagnostic to %s issue. Diagnostic %s issue. Test: %s',
			count($conflicts_found),
			empty($conflicts_found) ? 'none' : implode('; ', $conflicts_found),
			$expected_issue ? 'FIND' : 'NOT find',
			$diagnostic_has_issue ? 'FOUND' : 'DID NOT find',
			$test_passes ? 'PASS' : 'FAIL'
		);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
