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

			$src_define = $this->get_src_define();
			if( '' !== $src_define ) {

				$this->add_src_define( $src_define );
				$this->update_plugin_init_file( $blex_info, $src_define );
			}

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

	private function get_src_define() {

		$src_define = null;
		$plugin_file_spec = $this->_current_folder.DIRECTORY_SEPARATOR.'plugin.php';
		$plugin_content = file_get_contents( $plugin_file_spec );
		$plugin_name_matches = [];
		preg_match( '/Plugin Name:\s.*\s/U', $plugin_content, $plugin_name_matches );
		if( count( $plugin_name_matches ) > 0 ) {

			$plugin_name = str_replace( 'plugin name:', '', strtolower( $plugin_name_matches[0] ) );
			$plugin_name = str_replace( '-', '_', $plugin_name );
			$plugin_name = str_replace( ' ', '_', trim( $plugin_name ) );
			$plugin_name = strtoupper( $plugin_name );
			$src_define = $plugin_name.'_SRC';

		} else {

			throw new FoFo_Blex_Command_Exception( 'The plugin name could not be found in the plugin.php file. Some blex functions may not work correctly.' );
		}

		return $src_define;
	}

	private function add_src_define( $src_define ) {

		$plugin_file_spec = $this->_current_folder.DIRECTORY_SEPARATOR.'plugin.php';
		$plugin_content = file_get_contents( $plugin_file_spec );
		$plugin_content = $plugin_content.PHP_EOL."if( !defined( '".$src_define."' ) ) {".PHP_EOL.
			"\tdefine( '".$src_define."', plugin_dir_path( __FILE__ ).'src'  );".PHP_EOL.
			"}".PHP_EOL;

		file_put_contents( $plugin_file_spec, $plugin_content );
	}

	private function update_plugin_init_file( $blex_info, $src_define ) {

		//At this point we will only have one block so we can safely take the
		//first block

		$block = $blex_info->blocks[0];
		$init_file_spec = $this->_current_folder.DIRECTORY_SEPARATOR.$block->plugin_data->location;
		$content = file_get_contents( $init_file_spec );
		$content = str_replace( 'dirname( __FILE__ )', $src_define, $content );
		file_put_contents( $init_file_spec, $content );
	}
}
