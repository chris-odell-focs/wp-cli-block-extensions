<?php

namespace FoFo_Blex;

class FoFo_Blex_Info {

	private $_properties = [];
	private $_file_path;
	private $_template;
	private $_target_directory = null;
	private $_plugin_content = null;

	public function __construct( $file_path ) {

		$this->_file_path = $file_path;
		$this->_target_directory = dirname( $this->_file_path );
	} 

	public function create( $template_name, $template ) {

		$this->_template = $template;

		$blex_import = [];

		$blex_import[ 'template' ] = $template_name;
		$blex_import[ 'config_directory' ] = $this->check_folder( $this->_template[ 'config' ][ 'key' ], $this->_template[ 'config' ][ 'location' ] );
		$blex_import[ 'distributon_directory' ] = $this->check_folder( $this->_template[ 'dist' ][ 'key' ], $this->_template[ 'dist' ][ 'location' ] );
		$blex_import[ 'imports_file' ] = $this->check_file( $this->_template[ 'imports' ][ 'key' ], $this->_template[ 'imports' ][ 'location' ] );
		$blex_import[ 'source_folder' ] = $this->check_folder( $this->_template[ 'src' ][ 'key' ], $this->_template[ 'src' ][ 'location' ] );
		$blex_import[ 'blocks' ] = $this->init_block( $blex_import[ 'source_folder' ] );

		$this->populate( $blex_import );
	}

	public function read() {

		if( file_exists( $this->_file_path ) ) {

			$blex_info_json = file_get_contents( $this->_file_path );
			$blex_info = json_decode( $blex_info_json, true );

			$this->populate( $blex_info );

		} else {
			throw new \Exception('The file '.$this->_file_path.' does not exist');
		}
	}

	public function write() {

		$encoded_block_data = json_encode( $this->_properties, JSON_PRETTY_PRINT );
		file_put_contents( $this->_target_directory.DIRECTORY_SEPARATOR.'blex.info.json', $encoded_block_data );
	}

	private function populate( $blex_info ) {

		$this->_properties[ 'info_version' ] = '1.0';
		$this->_properties[ 'template' ] = $blex_info[ 'template' ];
		$this->_properties[ 'config_directory' ] = $blex_info[ 'config_directory' ];
		$this->_properties[ 'distributon_directory' ] = $blex_info[ 'distributon_directory' ];
		$this->_properties[ 'imports_file' ] = $blex_info[ 'imports_file' ];
		$this->_properties[ 'source_folder' ] = $blex_info[ 'source_folder' ];

		foreach( $blex_info[ 'blocks' ] as $block ) {

			$block_data = new \stdClass();
			$block_data->container_directory = $block[ 'container_directory' ];
			$block_data->registration_file = $block[ 'registration_file' ];
			$block_data->styles = $block[ 'styles' ];
			$block_data->namespace = $block[ 'namespace' ];
			
			$init_data = new \stdClass();
			$init_data->location = $block[ 'plugin_data' ][ 'location' ];
			$init_data->namespace = $block[ 'plugin_data' ][ 'namespace' ];
			$init_data->style = $block[ 'plugin_data' ][ 'style' ];
			$init_data->editor_script = $block[ 'plugin_data' ][ 'editor_script' ];
			$init_data->editor_style = $block[ 'plugin_data' ][ 'editor_style' ];
			$init_data->init_hook = $block[ 'plugin_data' ][ 'init_hook' ];

			$block_data->plugin_data = $init_data;

			$this->_properties[ 'blocks' ][] = $block_data;
		}
	}

	public function __get( $name ) {

		if( !isset( $this->_properties[ $name ] ) ) {

			throw new \Exception( 'Blex Info Property '.$name.' not found' );
		}

		return $this->_properties[ $name ];
	}

	public function __set( $name, $value ) {

		if( !isset( $this->_properties[ $name ] ) ) {

			throw new \Exception( 'Blex Info Property '.$name.' not found' );
		}

		$this->_properties[ $name ] = $value;
	}

	private function check_folder( $key, $location ) {

		return $this->check_file( $key, $location, 'folder' );
	}

	private function check_file( $key, $location, $type = 'file' ) {

		if( !file_exists( $this->_target_directory.DIRECTORY_SEPARATOR.$location ) ) {

			throw new FoFo_Blex_Command_Exception( "The '".$key."' ".$type." does not exist but is expected for this template." );
		}

		return $location;
	}

	private function extract_distributables_slugs() {

		$reg_block_type;
		preg_match( '/register_block_type\([\S\s]*\);/mU', $this->_plugin_content, $reg_block_type );

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

	private function get_init_hook() {

		$nsmatch = [];
		preg_match( "/function[\s][\S\s]*\(\)[\s]*{/m", $this->_plugin_content, $nsmatch );

		$target_value = isset( $nsmatch[0] ) ? $nsmatch[0] : '';
		$target_value = str_replace("function", '', $target_value);
		$target_value = str_replace("()", '', $target_value);
		$target_value = str_replace("{", '', $target_value);

		return trim( $target_value );
	}

	private function init_block( $src_folder ) {

		$blocks = [];
		$start_folder = $this->_target_directory.DIRECTORY_SEPARATOR.$src_folder.DIRECTORY_SEPARATOR.'*';
		$subfolders = glob($start_folder, GLOB_ONLYDIR|GLOB_NOSORT);
		foreach( $subfolders as $subfolder ) {

			$block_data = [];
			$init_meta = [];

			$init_meta[ 'location' ] = $this->check_file( $this->_template[ 'init-location' ][ 'key' ], $this->_template[ 'init-location' ][ 'location' ] );

			$this->_plugin_content = file_get_contents( $this->_target_directory.DIRECTORY_SEPARATOR.$init_meta[ 'location' ] );

			$dist_slugs = $this->extract_distributables_slugs();
			$init_meta[ 'namespace' ] = $dist_slugs[ 'namespace' ];
			$init_meta[ 'style' ] = $dist_slugs[ 'style' ];
			$init_meta[ 'editor_script' ] = $dist_slugs[ 'editor_script' ];
			$init_meta[ 'editor_style' ] = $dist_slugs[ 'editor_style' ];
			$init_meta[ 'init_hook' ] = $this->get_init_hook();
			
			$src_file = $subfolder.DIRECTORY_SEPARATOR.$this->_template[ 'block-src' ];
			if( !file_exists( $src_file ) ) {
				$src_file = '';
			}

			$css_pattern = '*'.$this->_template[ 'block-style-ext' ];
			$css_files = glob( $subfolder.DIRECTORY_SEPARATOR.$css_pattern, 0 );

			$css_files_fixed = [];
			foreach( $css_files as $css_file ) {

				$css_files_fixed[] = str_replace( $this->_target_directory.DIRECTORY_SEPARATOR, '', $css_file );
			}

			$fixed_src = str_replace( $this->_target_directory, '.', $src_file );

			$block_data[ 'registration_file' ] = str_replace( $this->_target_directory.DIRECTORY_SEPARATOR, '', $src_file );
			$block_data[ 'container_directory' ] = $this->check_file( $this->_template[ 'block-container-dir' ][ 'key' ], $this->_template[ 'block-container-dir' ][ 'location' ] );
			$block_data[ 'styles' ] = $css_files_fixed;
			$block_data[ 'namespace' ] = '';
			$block_data[ 'plugin_data' ] = $init_meta;
			
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
}
