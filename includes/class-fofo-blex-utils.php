<?php

namespace FoFo_Blex;

class FoFo_Blex_Utils {

	public static function in_wp_plugins_sub_folder( $idx, $folder ) {

		$path_parts = explode( DIRECTORY_SEPARATOR, $folder );

		$path_parts_length = count( $path_parts );
		$have_path_parts = $path_parts_length > $idx;
		$plugin_folder = $have_path_parts ? $path_parts[ $path_parts_length - ($idx - 1) ] : '';
		$wp_config_path = $have_path_parts ?
			implode( DIRECTORY_SEPARATOR, array_slice( $path_parts, 0, $path_parts_length - $idx ) ).
				DIRECTORY_SEPARATOR.
				'wp-config.php' :
			'';

		if( strtolower( $plugin_folder ) === 'plugins' && file_exists( $wp_config_path )) {

			return true;
		}

		return false;
	}

	public static function report( $message ) {

		\WP_CLI::log($message);
	}

	public static function warn( $message ) {

		\WP_CLI::warning($message);
	}

	public static function error( $message ) {

		\WP_CLI::error($message);
	}

	public static function success( $message ) {

		\WP_CLI::success($message);
	}
}
