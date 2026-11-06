<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
interface Module_Interface {
	public function id();
	public function name();
	public function description();
	public function is_enabled();
	public function boot();
}
