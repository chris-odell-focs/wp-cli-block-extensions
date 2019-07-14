<?php

namespace FoFo_Blex;

class FoFo_Blex_Rename extends FoFo_Blex_Command {

	private $_current_folder;

	public function block( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$this->_current_folder = isset( $assoc_args[ 'working_dir' ] ) ? $assoc_args[ 'working_dir' ] : getcwd();
			if( !file_exists( $this->_current_folder.'/blex.info.json' ) ) {

				throw new FoFo_Blex_Command_Exception( "Could not find the 'blex.info.json' file which is required to continue." );
			}

		});

	}
}
