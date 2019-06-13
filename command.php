<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}


spl_autoload_register(function ($class){

	$namspace = 'FoFo_Blex\\';
	if( false !== strpos( $class, $namspace ) ) {

		$require_types = [ 'class', 'abstract', 'interface' ];

		$class = str_replace( $namspace, '', $class );
		$class_stub = strtolower( str_replace( '_', '-', $class ).'.php' );
		$_s = DIRECTORY_SEPARATOR;

		$base_dir = dirname( __FILE__ ).$_s.'commands'.$_s.'core'.$_s;

		foreach( $require_types as $require_type ) {

			if( file_exists( $base_dir.$require_type.'-'.$class_stub ) ) {

				require_once( $base_dir.$require_type.'-'.$class_stub );
			}
		}
	}
});

WP_CLI::add_command( 'blex', new \FoFo_Blex\FoFo_Blex() );
WP_CLI::add_command( 'blex cgb', new \FoFo_Blex\FoFo_Blex_CGB() );
