<?php

namespace FoFo_Blex;

/**
* Lightweight wrapper around the create-guten-block dev-toolkit.
*
* ## Acknowledgements
*
*    This wp-cli package would not be possible without the help and amazing effort
*    supplied by Ahmad Awais(@MrAhmadAwais). Please give him some love and help support
*    this amazing piece of software.
*
* ## Examples
*
*    # Start, compile and watch
*    $ wp blex cgb start
*	 Success: The create-guten-block output while running
*
*    # Build for production
*    $ wp blex cgb build
*	 Success: Creation of js files ready for distribution
*
*    # Eject from create-guten-block
*    $ wp blex cgb eject
*	 Success: 'create-guten-block' eject process output is displayed.
*
* ## Notes
*
*    For more in-depth information on the create-guten-block dev-toolkit
*    please visit https://github.com/ahmadawais/create-guten-block
* 
*/
class FoFo_Blex_CGB {

	/**
	* Creates a block using create-guten-block
	*
	* ## OPTIONS
	*
	* <name>
	* : The name of the block to create
	*
	* ## Notes
	*
	*    This command needs to be run in the plugins folder, and if the command
	*    is not run in the plugins folder a message is displayed warning the user.
	*    see https://github.com/ahmadawais/create-guten-block for more information
	*    about using create-guten-block.
	*
	* ## Examples
	*
	*    # Create a block
	*    $ wp blex cgb my-test-block
	*	 Success: A new ESNext style block created in a sub directory of the Plugins folder called my-test-block
	*
	*/
	public function create($args) {

		if( $this->do_node_check() ) {

			if( $this->in_wp_plugins_folder() ) {

				if( count( $args ) > 0  && trim( $args[0] ) !== '' ) {
					passthru( 'npx create-guten-block '.$args[0] );

					WP_CLI::log('');
					WP_CLI::success('*********************************************************');
					WP_CLI::success('**                                                     **');
					WP_CLI::success('**      YOU CAN RUN THE ABOVE COMMANDS FROM BLEX       **');
					WP_CLI::success('**                                                     **');
					WP_CLI::success('*********************************************************');
					WP_CLI::log('');
					WP_CLI::log('Because you have created the block using blex the following commands above can be run');
					WP_CLI::log('with the following wp blex commands :-');
					WP_CLI::log('');
					WP_CLI::log('npm start => wp blex cgb start');
					WP_CLI::log('');
					WP_CLI::log('npm run build => wp blex cgb build');
					WP_CLI::log('');
					WP_CLI::log('npm run eject => wp blex cgb eject');
					WP_CLI::log('');
					WP_CLI::success('** THANK YOU FOR USING BLEX WE HOPE IT MAKES YOUR DAY EASIER **');
					WP_CLI::log('');


				} else {
					WP_CLI::error( 'No name was supplied for the block.' );
				}
				
			} else {
				WP_CLI::error( 'You need to be in the plugins folder of a WordPress installation to run that command.' );
			}

			
		}

	}

	/**
	* Compiles the block javascript and watches for changes.
	*
	* ## Notes
	*
	*    This command needs to be run in the folder of the block
	*    e.g. To run this for a block called my-test-block then The
	*    command would need to be run in the folder of the same name
	*    (assuming the block name and containing folder name are the same)
	*    see https://github.com/ahmadawais/create-guten-block for more information.
	*
	* ## Examples
	*
	*    # Start, compile and watch
	*    $ wp blex cgb start
	*	 Success: The create-guten-block output while running
	*
	*/
	public function start() {

		if( $this->do_node_check() ) {

			passthru( 'npm start' );
		}	
	}

	/**
	* Compiles and minifies the block javascript ready for distribution
	*
	* ## Notes
	*
	*    This command needs to be run in the folder of the block
	*    e.g. To run this for a block called my-test-block then The
	*    command would need to be run in the folder of the same name
	*    (assuming the block name and containing folder name are the same)
	*    see https://github.com/ahmadawais/create-guten-block for more information.
	*
	* ## Examples
	*
	*    # Build for production
	*    $ wp blex cgb build
	*	 Success: Creation of js files ready for distribution
	*
	*/
	public function build() {

		if( $this->do_node_check() ) {

			passthru( 'npm run build' );
		}	
	}

	/**
	* Ejects the block from create-guten-block
	*
	* ## Notes
	*
	*    Be aware this is a one way operation. Once a block
	*    is ejected then everything associated with the block
	*    (build scripts, node module dependencies etc) has to be
	*    maintained by you.
	*    see https://github.com/ahmadawais/create-guten-block for more information.
	*
	* ## Examples
	*
	*    # Eject from create-guten-block
	*    $ wp blex cgb eject
	*	 Success: 'create-guten-block' eject process output is displayed.
	*
	*/
	public function eject() {

		if( $this->do_node_check() ) {

			passthru( 'npm run eject' );
		}	
	}

	private function do_node_check() {

		$result = true;

		$report = WP_CLI::launch( 'node -v', false, true );
		if( '' !== $report->stderr ) {

			$result = false;
			WP_CLI::log('');
			WP_CLI::warning('Node.js and NPM are not installed, and are required to continue.' );
			WP_CLI::log('');
			WP_CLI::log('*************************************************************************************************************************' );
			WP_CLI::log('**                                                                                                                     **' );
			WP_CLI::log('** Blex works best with Node version >= 8 and npm version >= 5.3                                                       **' );
			WP_CLI::log('** Please go to https://nodejs.org/en/download/ for more information.                                                  **' );
			WP_CLI::log('** For Linux distros please check the version of Node.js in the repository before installing using a package manager.  **' );
			WP_CLI::log('**                                                                                                                     **' );
			WP_CLI::log('*************************************************************************************************************************' );
			WP_CLI::log('');
		}

		return $result;

	}

	private function in_wp_plugins_folder() {

		$path_parts = explode( DIRECTORY_SEPARATOR, getcwd() );

		$path_parts_length = count( $path_parts );
		$have_path_parts = $path_parts_length > 2;
		$plugin_folder = $have_path_parts ? $path_parts[ $path_parts_length - 1 ] : '';
		$wp_config_path = $have_path_parts ?
			implode( DIRECTORY_SEPARATOR, array_slice( $path_parts, 0, $path_parts_length - 2 ) ).
				DIRECTORY_SEPARATOR.
				'wp-config.php' :
			'';

		if( strtolower( $plugin_folder ) === 'plugins' && file_exists( $wp_config_path )) {

			return true;
		}

		return false;
	}

}
