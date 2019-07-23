<?php

namespace FoFo_Blex;

class FoFo_Blex_Import extends FoFo_Blex_Command {

	private $_args;
	private $_assoc_args;

	private $_cgb_template;
	private $_cgb_ejected_template;
	private $_template;

	private $_current_template_name;

	private $_current_folder;

	public function __construct( $args, $assoc_args ) {

		$this->_args = $args;
		$this->_assoc_args = $assoc_args;

		$this->build_cgb_template();
		$this->build_cgb_ejected_template();

		$this->set_template( isset( $assoc_args[ 'template' ] ) ? $assoc_args[ 'template' ] : 'cgb' );
		$this->_current_folder = isset( $assoc_args[ 'working_dir' ] ) ? $assoc_args[ 'working_dir' ] : getcwd();
	}

	private function build_cgb_template() {

		$this->_cgb_template[ 'config' ] = [ 'key' => 'config', 'location' => 'node_modules/cgb-scripts/config' ];
		$this->_cgb_template[ 'dist' ] = [ 'key' => 'dist', 'location' => 'dist' ];
		$this->_cgb_template[ 'imports' ] = [ 'key' => 'blocks.js', 'location' => 'src/blocks.js' ];
		$this->_cgb_template[ 'init-location' ] = [ 'key' => 'init.php', 'location' => 'src/init.php' ];
		$this->_cgb_template[ 'src' ] = [ 'key' => 'src', 'location' => 'src' ];
		$this->_cgb_template[ 'block-style-ext' ] = '.scss';
		$this->_cgb_template[ 'block-src' ] = 'block.js';
		$this->_cgb_template[ 'block-container-dir' ] = [ 'key' => 'container_dir', 'location' => 'src/block' ];
	}

	private function build_cgb_ejected_template() {

		$this->_cgb_ejected_template[ 'config' ] = [ 'key' => 'config', 'location' => 'config' ];
		$this->_cgb_ejected_template[ 'dist' ] = [ 'key' => 'dist', 'location' => 'dist' ];
		$this->_cgb_ejected_template[ 'imports' ] = [ 'key' => 'blocks.js', 'location' => 'src/blocks.js' ];
		$this->_cgb_ejected_template[ 'init-location' ] = [ 'key' => 'init.php', 'location' => 'src/init.php' ];
		$this->_cgb_ejected_template[ 'src' ] = [ 'key' => 'src', 'location' => 'src' ];
		$this->_cgb_ejected_template[ 'block-style-ext' ] = '.scss';
		$this->_cgb_ejected_template[ 'block-src' ] = 'block.js';
		$this->_cgb_ejected_template[ 'block-container-dir' ] = [ 'key' => 'container_dir', 'location' => 'src/block' ];
	}

	private function set_template( $template ) {

		$this->_current_template_name = $template;
		$this->_template = null;

		switch( $template ) {
			case 'cgb':
					$this->_template = $this->_cgb_template;
				break;
			case 'cgb-ejected':
					$this->_template = $this->_cgb_ejected_template;
				break;
		}
	}

	public function do_init() {

		$this->execute( $this->_args, $this->_assoc_args, function( $args, $assoc_args ) {

			FoFo_Blex_Utils::report( 'Using template '.$this->_current_template_name );

			if( $this->_template === null ) {

				throw new FoFo_Blex_Command_Exception( "The template '".$this->_current_template_name."' is not a recognised template and cannot be used" );
			}

			if( !FoFo_Blex_Utils::in_wp_plugins_sub_folder(3, $this->_current_folder) ) {

				throw new FoFo_Blex_Command_Exception( 'You do not appear to be in a plugin folder' );
			}

			$blex_info = new FoFo_Blex_Info( $this->_current_folder.DIRECTORY_SEPARATOR.'blex.info.json' );
			$blex_info->create( $this->_current_template_name, $this->_template );

			FoFo_Blex_Utils::report( "'config' value=".$blex_info->config_directory );
			FoFo_Blex_Utils::report( "'dist' value=".$blex_info->distributon_directory );
			FoFo_Blex_Utils::report( "'imports' value=".$blex_info->imports_file );
			FoFo_Blex_Utils::report( "'src' value=".$blex_info->source_folder );

			foreach( $blex_info->blocks  as $block ) {
				
				FoFo_Blex_Utils::report( 'Imported block '.$block->namespace );
			}

			FoFo_Blex_Utils::report( 'Saving blex.info.json' );
			$blex_info->write();
		});
	}
}
