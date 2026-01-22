<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Citation_Expansion extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-citations', 'title' => __('Citation Count & Quality Growth', 'wpshadow'), 'description' => __('Tracks total citations on high-authority directories. Citation gaps vs competitors = local ranking deficit.', 'wpshadow'), 'severity' => 'medium', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/citations/', 'training_link' => 'https://wpshadow.com/training/directory-submission/', 'auto_fixable' => false, 'threat_level' => 6]; } }
