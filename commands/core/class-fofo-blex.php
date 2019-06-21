<?php

namespace FoFo_Blex;

/**
* Block Extensions are a set of wp-cli command line utilities to make it easier and quicker to build gutenberg blocks.
*
* ## Acknowledgements
*
*    These utilities would not be available without the fantastic work by
*    all the team at https://wp-cli.org/ (@wpcli)
*/
class FoFo_Blex extends FoFo_Blex_Command {

	const VERSION = '1.0.3';

	/**
	* Get information for the BlocExtensions package
	*
	* ## Examples
	*
	*	$ wp blex info
	*/
	public function info() {

		$this->execute( [], [], function( $args, $assoc_args ) {

			\WP_CLI::log('Current blex version '.self::VERSION);

		});

	}
}
