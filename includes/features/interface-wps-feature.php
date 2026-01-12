<?php
/**
 * Feature Interface
 *
 * Defines the contract for feature objects managed by the registry.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface WPS_Feature_Interface {
	public function get_id(): string;

	public function get_name(): string;

	public function get_description(): string;

	public function get_scope(): string;

	public function get_hub(): ?string;

	public function get_spoke(): ?string;

	public function get_version(): string;

	public function get_default_state(): bool;

	public function get_widget_group(): string;

	public function get_widget_label(): string;

	public function get_widget_description(): string;
}
