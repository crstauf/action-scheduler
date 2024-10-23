<?php

/**
 * Class ActionScheduler_Versions
 */
class ActionScheduler_Versions {
	/**
	 * @var ActionScheduler_Versions
	 */
	private static $instance = NULL;

	/** @var array<string, callable> */
	private $versions = array();
	private $sources  = array();

	/**
	 * Register version's callback.
	 *
	 * @param string   $version_string          Action Scheduler version.
	 * @param callable $initialization_callback Callback to initialize the version.
	 */
	public function register( $version_string, $initialization_callback ) {
		if ( isset($this->versions[$version_string]) ) {
			return FALSE;
		}

		$backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$source    = $backtrace[0]['file'];

		$this->versions[$version_string] = $initialization_callback;
		$this->sources[$source] = $version_string;
		return TRUE;
	}

	/**
	 * Get all versions.
	 */
	public function get_versions() {
		return $this->versions;
	}

	public function get_sources() {
		return $this->sources;
	}

	/**
	 * Get latest version registered.
	 */
	public function latest_version() {
		$keys = array_keys($this->versions);
		if ( empty($keys) ) {
			return false;
		}
		uasort( $keys, 'version_compare' );
		return end($keys);
	}

	/**
	 * Get callback for latest registered version.
	 */
	public function latest_version_callback() {
		$latest = $this->latest_version();
		if ( empty($latest) || !isset($this->versions[$latest]) ) {
			return '__return_null';
		}
		return $this->versions[$latest];
	}

	/**
	 * @return ActionScheduler_Versions
	 * @codeCoverageIgnore
	 */
	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * @codeCoverageIgnore
	 */
	public static function initialize_latest_version() {
		$self = self::instance();
		call_user_func($self->latest_version_callback());
	}
}
