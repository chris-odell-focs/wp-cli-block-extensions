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
	}

	private function build_cgb_ejected_template() {

		$this->_cgb_ejected_template[ 'config' ] = [ 'key' => 'config', 'location' => 'config' ];
		$this->_cgb_ejected_template[ 'dist' ] = [ 'key' => 'dist', 'location' => 'dist' ];
		$this->_cgb_ejected_template[ 'imports' ] = [ 'key' => 'blocks.js', 'location' => 'src/blocks.js' ];
		$this->_cgb_ejected_template[ 'init-location' ] = [ 'key' => 'init.php', 'location' => 'src/init.php' ];
		$this->_cgb_ejected_template[ 'src' ] = [ 'key' => 'src', 'location' => 'src' ];
		$this->_cgb_ejected_template[ 'block-style-ext' ] = '.scss';
		$this->_cgb_ejected_template[ 'block-src' ] = 'block.js';
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

			$this->report( 'Using template '.$this->_current_template_name );

			if( $this->_template === null ) {

				throw new FoFo_Blex_Command_Exception( "The template '".$this->_current_template_name."' is not a recognised template and cannot be used" );
			}

			if( !FoFo_Blex_Utils::in_wp_plugins_sub_folder(3, $this->_current_folder) ) {

				throw new FoFo_Blex_Command_Exception( 'You do not appear to be in a plugin folder' );
			}

			$blex_import = [];

			$blex_import[ 'config' ] = $this->check_folder( $this->_template[ 'config' ][ 'key' ], $this->_template[ 'config' ][ 'location' ] );
			$this->report( "'config' value=".$blex_import[ 'config' ] );

			$blex_import[ 'dist' ] = $this->check_folder( $this->_template[ 'dist' ][ 'key' ], $this->_template[ 'dist' ][ 'location' ] );
			$this->report( "'dist' value=".$blex_import[ 'dist' ] );

			$blex_import[ 'imports' ] = $this->check_file( $this->_template[ 'imports' ][ 'key' ], $this->_template[ 'imports' ][ 'location' ] );
			$this->report( "'imports' value=".$blex_import[ 'imports' ] );

			$blex_import[ 'init' ][ 'location' ] = $this->check_file( $this->_template[ 'init-location' ][ 'key' ], $this->_template[ 'init-location' ][ 'location' ] );
			$this->report( "'init location' value=".$blex_import[ 'init' ][ 'location' ] );

			$dist_slugs = $this->extract_distributables_slugs( $blex_import[ 'init' ][ 'location' ] );
			$blex_import[ 'init' ][ 'namespace' ] = $dist_slugs[ 'namespace' ];
			$this->report( "'init namespace' value=".$blex_import[ 'init' ][ 'namespace' ] );

			$blex_import[ 'init' ][ 'style' ] = $dist_slugs[ 'style' ];
			$this->report( "'init style' value=".$blex_import[ 'init' ][ 'style' ] );

			$blex_import[ 'init' ][ 'editor_script' ] = $dist_slugs[ 'editor_script' ];
			$this->report( "'init namespace' value=".$blex_import[ 'init' ][ 'namespace' ] );

			$blex_import[ 'init' ][ 'editor_style' ] = $dist_slugs[ 'editor_style' ];
			$this->report( "'init editor_script' value=".$blex_import[ 'init' ][ 'editor_script' ] );

			$blex_import[ 'src' ] = $this->check_folder( $this->_template[ 'src' ][ 'key' ], $this->_template[ 'src' ][ 'location' ] );
			$this->report( "'src' value=".$blex_import[ 'src' ] );

			$this->report( 'Importing Blocks' );
			$blex_import[ 'blocks' ] = $this->init_block( $blex_import[ 'src' ] );

			foreach( $blex_import[ 'blocks' ]  as $block ) {
				
				$this->report( 'Imported block '.$block[ 'namespace' ] );
			}

			$this->report( 'Saving blex.info.json' );
			$this->save_blocks( $blex_import );

		});
	}

	private function check_folder( $key, $location ) {

		return $this->check_file( $key, $location, 'folder' );
	}

	private function check_file( $key, $location, $type = 'file' ) {

		if( !file_exists( $this->_current_folder.DIRECTORY_SEPARATOR.$location ) ) {

			throw new FoFo_Blex_Command_Exception( "The '".$key."' ".$type." does not exist but is expected for this template." );
		}

		return $location;
	}

	private function extract_distributables_slugs( $init_file ) {

		$content = file_get_contents( $this->_current_folder.DIRECTORY_SEPARATOR.$init_file );

		$reg_block_type;
		preg_match( '/register_block_type\([\S\s]*\);/mU', $content, $reg_block_type );

		if( isset( $reg_block_type[0] ) ) {

			$style = $this->get_key_value_pair( $reg_block_type[0], 'style');
			$ed_script = $this->get_key_value_pair( $reg_block_type[0], 'editor_script');
			$ed_style = $this->get_key_value_pair( $reg_block_type[0], 'editor_style');
			$ns = $this->get_namespace_from_php_ini( $reg_block_type[0] );
		}

		$result = [
			'namespace' => $ns,
			'style' => $style,
			'editor_script' => $ed_script,
			'editor_style' => $ed_style
		];

		return $result;
	}

	private function get_key_value_pair( $raw, $type ) {

		$target_match;
		preg_match( "/'".$type."[\S\s]*=>[\S\s]*',/mU", $raw, $target_match );
		$target_value = isset( $target_match[0] ) ?  str_replace("'".$type."'", '', $target_match[0] ) : '';
		$target_value = str_replace("'".$type."'", '', $target_value );
		$target_value = str_replace(',', '', str_replace("=>", '', $target_value ));
		$target_value = str_replace("'", '', $target_value);

		return trim( $target_value );
	}

	private function get_namespace_from_php_ini( $raw ) {

		$nsmatch = [];
		preg_match( "/\([\S\s]*'[\S\s]*'/mU", $raw, $nsmatch );

		$target_value = isset( $nsmatch[0] ) ? $nsmatch[0] : '';
		$target_value = str_replace("'", '', $target_value);
		$target_value = str_replace("(", '', $target_value);

		return trim( $target_value );
	}

	private function init_block( $src_folder ) {

		$blocks = [];
		$start_folder = getcwd().DIRECTORY_SEPARATOR.$src_folder.DIRECTORY_SEPARATOR.'*';
		$subfolders = glob($start_folder, GLOB_ONLYDIR|GLOB_NOSORT);
		foreach( $subfolders as $subfolder ) {

			$block_data = [];

			$src_file = $subfolder.DIRECTORY_SEPARATOR.$this->_template[ 'block-src' ];
			if( !file_exists( $src_file ) ) {
				$src_file = '';
			}

			$css_pattern = '*'.$this->_template[ 'block-style-ext' ];
			$css_files = glob( $subfolder.DIRECTORY_SEPARATOR.$css_pattern, 0 );

			$block_data[ 'src_file' ] = $src_file;
			$block_data[ 'styles' ] = $css_files;
			$block_data[ 'namespace' ] = '';
			
			if( '' !== $src_file ) {

				$file_content = file_get_contents( $src_file );
				$parser = new FoFo_Blex_Module_Parser( $file_content );
				$module = $parser->parse();
				$module->navigate_to( '/registerBlockType/arguments/0' );
				$block_data[ 'namespace' ] = str_replace( "'", '', $module->get_element() );
			}

			$blocks[] = $block_data;
		}

		return $blocks;
	}

	private function report( $message ) {

		\WP_CLI::log($message);
	}

	private function save_blocks( $block_data ) {

		$encoded_block_data = json_encode( $block_data );
		file_put_contents( $this->_current_folder.DIRECTORY_SEPARATOR.'blex.info.json', $encoded_block_data );

	}
}
