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

	const VERSION = '1.0.5';

	/**
	* Initialise a new block into blex by generating a blex.info.json file
	*
	* ## Notes
	*
	*	Needs to be in the plugin folder to run. The blex.info.json file
	*   holds information such as the location of the 'src' folder. This
	*	information is then used for other blex commands to manipulate the block.
	*
	* ## Options
	*
	*	[--template=<template>]
	*   : The template to use to initialise the new block, template can be one of cgb(default) or cgb-ejected
	*
	* ## Examples
	*
	*	$ wp blex initialise
	*   $ wp blex initialise --template=cgb
	*	$ wp blex initialise --template=cgb-ejected
	*/
	public function initialise( $args, $assoc_args ) {

		$importer = new FoFo_Blex_Import( $args, $assoc_args );
		$importer->do_init();
	}

	/**
	* Get information about the BlockExtensions package
	*
	* ## Examples
	*
	*    $ wp blex info
	*/
	public function info() {

		$this->execute( [], [], function( $args, $assoc_args ) {

			\WP_CLI::log('Current blex version '.self::VERSION);

		});

	}
}
