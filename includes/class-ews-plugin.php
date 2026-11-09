<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
require_once EWS_PATH . 'includes/interface-ews-module.php';
require_once EWS_PATH . 'includes/class-ews-module-manager.php';
require_once EWS_PATH . 'includes/class-ews-admin.php';
require_once EWS_PATH . 'modules/seo/class-ews-seo-module.php';
require_once EWS_PATH . 'modules/social-preview/class-ews-social-preview-module.php';
require_once EWS_PATH . 'modules/security/class-ews-security-module.php';
require_once EWS_PATH . 'modules/redirects/class-ews-redirects-module.php';
require_once EWS_PATH . 'modules/rss/class-ews-rss-module.php';
require_once EWS_PATH . 'modules/schema/class-ews-schema-module.php';
require_once EWS_PATH . 'modules/sitemap/class-ews-sitemap-module.php';
require_once EWS_PATH . 'modules/forms/class-ews-forms-module.php';
final class Plugin {
	private static $instance;
	private $modules;

	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		$this->modules = new Module_Manager();
	}

	/**
	 * Get the module manager instance.
	 *
	 * @return Module_Manager
	 */
	public function modules(): Module_Manager {
		return $this->modules;
	}

	/**
	 * Plugin activation lifecycle handler.
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Initialize default settings if not already present.
		$defaults = Admin::get_default_settings();
		if ( false === get_option( 'ews_settings' ) ) {
			add_option( 'ews_settings', $defaults );
		}

		// Store installed DB version for future schema migrations.
		if ( false === get_option( 'ews_version' ) ) {
			add_option( 'ews_version', EWS_VERSION );
		} else {
			update_option( 'ews_version', EWS_VERSION );
		}
	}

	/**
	 * Plugin deactivation lifecycle handler.
	 *
	 * @return void
	 */
	public static function deactivate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		// Flush rewrite rules in case sitemaps or redirects were active.
		flush_rewrite_rules();
	}

	public function boot() {
		$this->modules->register( new SEO_Module() );
		$this->modules->register( new Social_Preview_Module() );
		$this->modules->register( new Security_Module() );
		$this->modules->register( new Redirects_Module() );
		$this->modules->register( new RSS_Module() );
		$this->modules->register( new Schema_Module() );
		$this->modules->register( new Sitemap_Module() );
		$this->modules->register( new Forms_Module() );
		$this->modules->boot_enabled();
		if ( is_admin() ) { ( new Admin() )->boot(); }
	}
}
