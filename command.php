<?php

if (!function_exists('write_log')) {
	function write_log ( $log )  {
	    if ( true === WP_DEBUG ) {
	        if ( is_array( $log ) || is_object( $log ) ) {
	            error_log( print_r( $log, true ) );
	        } else {
	            error_log( $log );
	        }
	    }
	}
}

if (!function_exists('write_test_log')) {
	function write_test_log ( $log )  {

		$log_name = '/tmp/test.log';
		$prefix = '['.date("D M d, Y G:i").'] -> ';

	    if ( is_array( $log ) || is_object( $log ) ) {
	        error_log( $prefix.print_r( $log, true ).PHP_EOL, 3, $log_name );
	    } else {
	        error_log( $prefix.$log.PHP_EOL, 3, $log_name );
	    }
	}
}

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once "vendor/autoload.php";

spl_autoload_register(function ($class){

	$namspace = 'FoFo_Blex\\';
	if( false !== strpos( $class, $namspace ) ) {

		$class = str_replace( $namspace, '', $class );
		$class_stub = strtolower( str_replace( '_', '-', $class ).'.php' );
		$_s = DIRECTORY_SEPARATOR;

		$require_types = [ 'class', 'abstract', 'interface' ];
		$folders = [ 'commands'.$_s.'core', 'includes' ];

		foreach( $folders as $folder_stub ) {

			$base_dir = dirname( __FILE__ ).$_s.$folder_stub.$_s;

			foreach( $require_types as $require_type ) {

				if( file_exists( $base_dir.$require_type.'-'.$class_stub ) ) {

					require_once( $base_dir.$require_type.'-'.$class_stub );
				}
			}
		}
	}
});

WP_CLI::add_command( 'blex', new \FoFo_Blex\FoFo_Blex() );
WP_CLI::add_command( 'blex cgb', new \FoFo_Blex\FoFo_Blex_CGB() );
WP_CLI::add_command( 'blex rename', new \FoFo_Blex\FoFo_Blex_Rename() );
