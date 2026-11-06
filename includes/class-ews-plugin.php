<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once EWS_PATH . 'includes/interface-ews-module.php';
require_once EWS_PATH . 'includes/class-ews-module-manager.php';
require_once EWS_PATH . 'includes/class-ews-admin.php';
require_once EWS_PATH . 'modules/seo/class-ews-seo-module.php';
require_once EWS_PATH . 'modules/social-preview/class-ews-social-preview-module.php';
final class Plugin {
	private static $instance;
	private $modules;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}
	private function __construct() { $this->modules = new Module_Manager(); }
	public function boot() {
		$this->modules->register( new SEO_Module() );
		$this->modules->register( new Social_Preview_Module() );
		$this->modules->boot_enabled();
		if ( is_admin() ) { ( new Admin() )->boot(); }
	}
}
