<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface WPSHADOW_Feature_Interface {
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

	public function get_license_level(): int;

	public function get_minimum_capability(): string;

	public function get_sub_features(): array;

	public function get_sub_feature_descriptions(): array;

	public function get_icon(): string;

	public function get_category(): string;

	public function get_priority(): int;

	public function get_dashboard(): string;

	public function get_widget_column(): string;

	public function get_widget_priority(): int;

	public function get_aliases(): array;
}
