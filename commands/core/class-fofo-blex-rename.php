<?php

namespace FoFo_Blex;

/**
 * Rename elements of a block
 * 
 * After creating aa block with, for example, create-guten-block, rename can be used to rename elements such as the namespace
 * 
 */
class FoFo_Blex_Rename extends FoFo_Blex_Command {

	private $_current_folder = null;
	private $_blex_info = null;

	const CONTAINER_DIRECTORY_ACTION = 'container-directory';
	const REGISTRATION_FILE_ACTION = 'registration-file';

	/**
	* Rename the top level import file
	*
	* ## Options
	*
	*	<name>
	*	: The new name of the import file
	*
	*	[--working_dir=<plugin director>]
	*	: The WordPress plugin directory to run in. If omitted defaults to the current working directory.
	*
	* ## Notes
	*
	*	Blex assumes the structure of a plugin to have one top level file
	*	which is used to import all blocks within a WordPress plugin.
	*
	* ## Examples
	*
	*	# Run rename in the current folder
	*	$ wp blex rename import-file imports.js
	*
	*	# Run rename targeting a specific plugin folder
	*	$ wp blex rename import-file imports.js --working_dir=<path-to-plugin-folder>
	*
	* @alias import-file
	*/
	public function import_file( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$this->_current_folder = $this->get_working_directory( $assoc_args );
			$this->check_for_blex_info( $this->_current_folder );

			$blex_info = $this->get_blex_info( $this->_current_folder );
			$blex_info->read();

			$new_imports_file = dirname( $blex_info->imports_file ).DIRECTORY_SEPARATOR.$args[0];

			$current_imports_full_path = $this->_current_folder.$blex_info->imports_file;
			$new_imports_full_path = $this->_current_folder.$new_imports_file;

			rename( $current_imports_full_path, $new_imports_full_path );

			if( $blex_info->template === 'cgb' || $blex_info->template === 'cgb-ejected' ) {
				
				$pathjs_path = $this->_current_folder.$blex_info->config_directory.DIRECTORY_SEPARATOR.'paths.js';
				if( file_exists( $pathjs_path ) ) {

					$path_file_content = file_get_contents( $pathjs_path );
					$path_file_content = str_replace( $blex_info->imports_file, $new_imports_file, $path_file_content );
					file_put_contents( $pathjs_path, $path_file_content );

				} else {

					FoFo_Blex_Utils::warn( "The config file 'paths.js' was not found. This could cause an issue building your block." );
				}
			}

			$blex_info->imports_file = $new_imports_file;
			$blex_info->write();
		});
	}

	/**
	 * Rename an element of a specific block.
	 * 
	 * ## Options
	 * 
	 *	<namespace>
	 *  : The namespace of the block e.g. cgb/block-blex-test-block
	 * 
	 *	<block-element>
	 *  : The block element to rename can be one of registration-file[rf] | container-directory[cd] | namespace[bns] | style-slug[sts] | editor-script-slug[ess] | editor-style-slug[ests] | init-hook[iho]
	 * 
	 *  <name>
	 *  : The new name of the block element
	 * 
	 * 	[--working_dir=<plugin director>]
	 *	: The WordPress plugin directory to run in. If omitted defaults to the current working directory.
	 *
	 * ## Notes
	 * 
	 * 	Each block element has a full name and a short version which can be used. e.g. when renaming the registration file block element then 'registration-file' can be used or 'rf'
	 * 
	 * ## Examples
	 * 
	 * 	1. Rename a registration file i.e. the JS file with registerBlockType
	 *		$ wp blex rename block 'cgb/block-blex-test-block' registration-file index.js
	 *		$ wp blex rename block 'cgb/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 *		$ wp blex rename block 'cgb/block-blex-test-block' rf index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 
	 * 	2. Rename a container directory i.e. the directory containing the registration JS file
	 * 
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' container-directory my-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' container-directory my-block --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' cd my-block --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 *
	 * 	3. Rename a block name space. i.e. the namespace declared in the JS file registerBlockType function and the PHP file register_block_type
	 * 
	 * 		$ wp wp blex rename block 'cgb/block-blex-test-block' namespace 'blex/block-blex-test-block'
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' namespace 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' bns 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 *  
	 * 	4. Rename a style slug i.e. the style slug in the plugin php file as an argument to register_block_type
	 * 
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' style-slug 'blex_test_block-blex-style-css'
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' style-slug 'blex_test_block-blex-style-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' sts 'blex_test_block-blex-style-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		
	 *  
	 * 	5. Rename an editor script slug i.e. the editor script slug in the plugin php file as an argument to register_block_type
	 * 
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' editor-script-slug 'blex_test_block-blex-block-js'
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' editor-script-slug 'blex_test_block-blex-block-js' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' ess 'blex_test_block-blex-block-js' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 
	 * 	6. Rename an editor style slug i.e. the editor style slug in the plugin php file as an argument to register_block_type
	 * 
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' editor-style-slug 'blex_test_block-blex-block-editor-css'
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' editor-style-slug 'blex_test_block-blex-block-editor-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' ests 'blex_test_block-blex-block-editor-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 
	 * 	7. Rename the hook(and function name) used in 'add_action( 'init', '<function-name>' )' in the PHP file
	 * 	
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' init-hook blex_test_block_blex_block_assets
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' init-hook blex_test_block_blex_block_assets --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 * 		$ wp blex rename block 'cgb/block-blex-test-block' iho blex_test_block_blex_block_assets --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block
	 */
	public function block( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$redirects = $this->get_redirect_for_action();
			$this->{$redirects[ $args[1] ]}( $args, $assoc_args );
			
		});
	}

	private function get_redirect_for_action() {

		$redirect_list = [];
		$redirect_list[ 'registration-file' ] = 'rename_registration_file_or_container_dir';
		$redirect_list[ 'rf' ] = 'rename_registration_file_or_container_dir';

		$redirect_list[ 'container-directory' ] = 'rename_registration_file_or_container_dir';
		$redirect_list[ 'cd' ] = 'rename_registration_file_or_container_dir';

		$redirect_list[ 'namespace' ] = 'block_namespace';
		$redirect_list[ 'bns' ] = 'block_namespace';

		$redirect_list[ 'style-slug' ] = 'style_slug';
		$redirect_list[ 'sts' ] = 'style_slug';

		$redirect_list[ 'editor-script-slug' ] = 'editor_script_slug';
		$redirect_list[ 'ess' ] = 'editor_script_slug';

		$redirect_list[ 'editor-style-slug' ] = 'editor_style_slug';
		$redirect_list[ 'ests' ] = 'editor_style_slug';

		$redirect_list[ 'init-hook' ] = 'init_hook';
		$redirect_list[ 'iho' ] = 'init_hook';

		return $redirect_list;
	}

	private function rename_registration_file_or_container_dir( $args, $assoc_args ) {

		$this->_current_folder = $this->get_working_directory( $assoc_args );
		$this->check_for_blex_info( $this->_current_folder );

		$namespace = $args[0];

		$action = strtolower( $args[1] );
		if( strtolower( $args[1] ) === 'cd' ) {
			$action = self::CONTAINER_DIRECTORY_ACTION;
		} else if( strtolower( $args[1] ) === 'rf' ) {
			$action = self::REGISTRATION_FILE_ACTION;
		}

		$to = $args[2];

		$blex_info = new FoFo_Blex_Info( $this->_current_folder.DIRECTORY_SEPARATOR.'blex.info.json' );
		$blex_info->read();

		$block_found = false;
		foreach( $blex_info->blocks as $block ) {

			if( $block->namespace == $namespace ) {

				$original_target = '';
				$updated_target = '';

				$current_folder = $this->_current_folder.DIRECTORY_SEPARATOR;
				$block_found = true;

				$target_dir = $current_folder.$block->container_directory;

				if( $action === self::CONTAINER_DIRECTORY_ACTION ) {

					$from_dir = $current_folder.$block->container_directory;
					if( !file_exists( $from_dir ) ) {
						throw new FoFo_Blex_Command_Exception( "The directory '".$block->container_directory."' could not be found." );
					}

					$to_dir = $current_folder.dirname( $block->container_directory ).DIRECTORY_SEPARATOR.$to;

					rename( $from_dir, $to_dir );

					$original_target = str_replace( dirname( $from_dir ), dirname( $block->container_directory ), $from_dir );
					$updated_target = str_replace( dirname( $to_dir ), dirname( $block->container_directory ), $to_dir );

				}

				if( $action === self::REGISTRATION_FILE_ACTION ) {

					$from_file = $current_folder.$block->registration_file;
					if( !file_exists( $from_file ) ) {
						throw new FoFo_Blex_Command_Exception( "The file '".$block->registration_file."' could not be found." );
					}

					$tofile = dirname( $from_file ).DIRECTORY_SEPARATOR.$to;
					rename( $from_file, $tofile );

					$original_target = str_replace( dirname( $from_file ), dirname( $block->registration_file ), $from_file );
					$updated_target = str_replace( dirname( $tofile ), dirname( $block->registration_file ), $tofile );
				}

				if( '' !== $original_target && '' !== $updated_target ) {

					$for_imports_original = str_replace( $blex_info->source_folder, '.', $original_target );
					$for_imports_updated = str_replace( $blex_info->source_folder, '.', $updated_target );

					$imports_file = $current_folder.$blex_info->imports_file;
					$imports = file_get_contents( $imports_file );
					$imports = str_replace( $for_imports_original, $for_imports_updated, $imports );
					file_put_contents( $imports_file, $imports );

					if( $action === self::REGISTRATION_FILE_ACTION ) {

						$block->registration_file = $updated_target;
					} else if( $action === self::CONTAINER_DIRECTORY_ACTION ) {

						$block->registration_file = str_replace( $block->container_directory, $updated_target, $block->registration_file );
						
						$updated_styles = [];
						foreach( $block->styles as $style_locaton ) {
							$updated_styles[] = str_replace( $block->container_directory, $updated_target, $style_locaton );
						}

						$block->styles = $updated_styles;
						$block->container_directory = $updated_target;
					}

					$blex_info->write();
				}
			}
		}

		if( !$block_found ) {

			FoFo_Blex_Utils::warn( "The block with namespace '".$namespace."' was not found" );
		}
	}

	private function block_namespace( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {
			
			$command_args = [
				$args[0], //namespace
				$args[0], //search
				$args[2], //replace
			];

			$this->do_replace_in_block( 'namespace', $command_args, $assoc_args );

			$command_args_2 = [
				$args[2], //namespace(The new namespace)
				'.wp-block-'.str_replace( "/", '-', $args[0] ), //search
				'.wp-block-'.str_replace( "/", '-', $args[2] ), //replace
			];

			$this->do_replace_in_block( 'namespace_styles', $command_args_2, $assoc_args );

		});
	}

	private function style_slug( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {
			
			$command_args = [
				$args[0], //namespace
				'', //search
				$args[2], //replace
			];

			$this->do_replace_in_block( 'style', $command_args, $assoc_args );
		});
	}

	private function editor_script_slug( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$command_args = [
				$args[0], //namespace
				'', //search
				$args[2], //replace
			];

			$this->do_replace_in_block( 'editor_script', $command_args, $assoc_args );
		});
	}
	
	private function editor_style_slug( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$command_args = [
				$args[0], //namespace
				'', //search
				$args[2], //replace
			];

			$this->do_replace_in_block( 'editor_style', $command_args, $assoc_args );
		});
	}

	private function init_hook( $args, $assoc_args ) {

		$this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

			$command_args = [
				$args[0], //namespace
				'', //search
				$args[2], //replace
			];

			$this->do_replace_in_block( 'init_hook', $command_args, $assoc_args );
		});
	}

	private function do_replace_in_block( $action, $command_args, $assoc_args ) {

		$this->_current_folder = $this->get_working_directory( $assoc_args );
		$this->check_for_blex_info( $this->_current_folder );

		$namespace = $command_args[0];
		$search = $command_args[1];
		$replace = $command_args[2];

		$blex_info = $this->get_blex_info( $this->_current_folder );
		$blex_info->read();

		$block_found = false;
		foreach( $blex_info->blocks as $block ) {

			if( $block->namespace == $namespace ) {

				$current_folder = $this->_current_folder;
				$block_found = true;

				$action_lists = $this->get_lists_for_do_replace( $current_folder, $block, $action );
				$file_list = $action_lists[ 'file_list' ];
				$property_list = $action_lists[ 'property_list' ];

				foreach( $file_list as $file => $message ) {

					if( file_exists( $file ) ) {

						$target_blex_info_property = $block;
						$prop_name;

						foreach( $property_list as $property ) {
							
							$child_properties = $property->children;
							while( count( $child_properties ) > 0 ) {

								$target_blex_info_property = $target_blex_info_property->{$property->name};
								$property = $child_properties[0];
								$child_properties = $property->children;
							}

							$prop_name = $property->name;
						}

						if( '' === $search && !empty( $prop_name ) ) {
							$search = $target_blex_info_property->{$prop_name};
						}

						$file_content = file_get_contents( $file );
						$file_content = str_replace( $search, $replace, $file_content );
						file_put_contents( $file, $file_content );

						if( !empty( $prop_name ) ) {
							$target_blex_info_property->{$prop_name} = $replace;
						}
						
					} else {

						FoFo_Blex_Utils::error( $message );
					}
				}

				$blex_info->write();
			}
		}

		if( !$block_found ) {

			FoFo_Blex_Utils::warn( "The block with namespace '".$namespace."' was not found." );
		}

	}

	private function get_lists_for_do_replace( $current_folder, $block, $action ) {
		
		$file_list = [];
		$property_list = [];
		switch( $action ) {
			case 'namespace' :
				$file_list = [
					$current_folder.$block->plugin_data->location => "The plugin file '".$block->plugin_data->location."' could not be found.",
					$current_folder.$block->registration_file => "The registration file '".$block->registration_file."' could not be found.",
				];
				
				$property = new \stdClass();
				$property->name = 'namespace';
				$property->children = [];

				$property_list[] = $property;

				break;
			
			case 'namespace_styles' :

				foreach( $block->styles as $style_file ) {

					$file_list[ $current_folder.$style_file ] = "The style file '".$current_folder.$style_file."' could not be found.";
				}
				break;

			case 'style' :
			case 'editor_script' :
			case 'editor_style' :
			case 'init_hook':
				$file_list = [
					$current_folder.$block->plugin_data->location => "The plugin file '".$block->plugin_data->location."' could not be found."
				];

				$property = new \stdClass();
				$property->name = 'plugin_data';
				$property->children = [];

				$p1_child = new \stdClass();
				$p1_child->children = [];
				$p1_child->name = $action;

				$property->children[] = $p1_child;				

				$property_list[] = $property;

				break;
		}

		return [
			'file_list' => $file_list,
			'property_list' => $property_list
		];
	}
}
