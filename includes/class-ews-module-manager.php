<?php
namespace Earthin\WPSuite;
if ( ! defined( 'ABSPATH' ) ) { exit; }
final class Module_Manager {
	/**
	 * Registered modules map [module_id => Module_Interface].
	 *
	 * @var array<string, Module_Interface>
	 */
	private $modules = array();

	/**
	 * Register a module instance.
	 *
	 * @param Module_Interface $module Module instance.
	 * @return void
	 */
	public function register( Module_Interface $module ) {
		$this->modules[ $module->id() ] = $module;
	}

	/**
	 * Check if a module is registered.
	 *
	 * @param string $id Module identifier.
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->modules[ $id ] );
	}

	/**
	 * Retrieve a registered module by its ID.
	 *
	 * @param string $id Module identifier.
	 * @return Module_Interface|null
	 */
	public function get( string $id ): ?Module_Interface {
		return $this->modules[ $id ] ?? null;
	}

	/**
	 * Check if a specific module is enabled.
	 *
	 * @param string $id Module identifier.
	 * @return bool
	 */
	public function is_enabled( string $id ): bool {
		$module = $this->get( $id );
		return $module ? $module->is_enabled() : false;
	}

	/**
	 * Boot all enabled modules safely.
	 *
	 * @return void
	 */
	public function boot_enabled() {
		foreach ( $this->modules as $module ) {
			if ( $module->is_enabled() ) {
				try {
					$module->boot();
				} catch ( \Throwable $e ) {
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( sprintf( '[Earthin WP Suite] Failed to boot module "%s": %s', $module->id(), $e->getMessage() ) );
					}
				}
			}
		}
	}

	/**
	 * Get all registered modules.
	 *
	 * @return array<string, Module_Interface>
	 */
	public function all(): array {
		return $this->modules;
	}
}
