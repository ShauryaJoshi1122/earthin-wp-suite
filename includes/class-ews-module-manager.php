<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Module_Manager {
	private $modules = array();
	public function register( Module_Interface $module ) {
		$this->modules[ $module->id() ] = $module;
	}
	public function boot_enabled() {
		foreach ( $this->modules as $module ) {
			if ( $module->is_enabled() ) { $module->boot(); }
		}
	}
	public function all() { return $this->modules; }
}
