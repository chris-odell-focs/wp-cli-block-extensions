<?php

namespace FoFo_Blex;

/**
 * Move the elements of a block
 */
class FoFo_Blex_Move extends FoFo_Blex_Command {

    private $_current_folder = null;
	private $_blex_info = null;

    /**
    * Move the elements associated with a block
    *
	* ## Options
    *
    *   <action>
    *   : The action to take, only 'block' is currently supported.
    *
	*   <namespace>
	*   : The namespace of the block e.g. cgb/block-blex-test-block
    *   
    *   <element>   
    *   : The element to move, if omitted then the default is to move the contianer directory of the block
    *
    *   <name>
    *   : The new element name.
    *
	*	[--working_dir=<plugin director>]
	*	: The WordPress plugin directory to run in. If omitted defaults to the current working directory.
	*
	* ## Notes
	*
    *	Only one element is available, 'plugin-file' if the element is omitted then the default action of moving 
    *   the block container directory will be assumed. 'plugin-file' can also be abbreviated to 'pf, and the move command can be abbreviated to 'mv'.
    *   The root for all folders used in the move command is the 'src' folder. i.e. using an argument of 'block' in a move command will refer to 'src/block'.
    *   A plugin file is the 'init.php' file used to define the block NOT the plugin.php file.
    *   A block cannot be moved to a sub folder of the current container directory i.e. a block in 'src/block' cannot be moved to 'src/block/sub-directory'
	*
	* ## Examples
	*
    *	1. # Move the pugin file for a block with the namespace 'cgb/block-blex-test-block' to a folder called 'block' under the src folder
    *
    *	    $ wp blex move block 'cgb/block-blex-test-block' plugin-file 'block'
    *       $ wp blex move block 'cgb/block-blex-test-block' pf 'block'
    *       $ wp blex mv block 'cgb/block-blex-test-block' pf 'block'
    *
    *   2. # Move the block with the namespace 'cgb/block-blex-test-block' to a folder called 'block'
    *
    *	    $ wp blex move block 'cgb/block-blex-test-block' 'block'
    *       $ wp blex mv block 'cgb/block-blex-test-block' 'block'
    *
    *   3. # Move the block with the namespace 'cgb/block-blex-test-block' to a folder called 'allblocks/block' under the src folder
    *
    *       $ wp blex move block 'cgb/block-blex-test-block' 'allblocks/block'
    *       $ wp blex mv block 'cgb/block-blex-test-block' 'allblocks/block'
    *
	*/
    public function block( $args, $assoc_args ) {

        $this->execute( $args, $assoc_args, function( $args, $assoc_args ) {

            $this->_current_folder = $this->get_working_directory( $assoc_args );
            $this->check_for_blex_info( $this->_current_folder );

            $blex_info = $this->get_blex_info( $this->_current_folder );
            $blex_info->read();
            
            $namespace = $args[0];
            $directory_arg = count( $args ) == 2 ? $args[1] : $args[2];
            $destination_directory = $this->_current_folder.$blex_info->source_folder.DIRECTORY_SEPARATOR.$directory_arg;
            
            $block_found = false;
            foreach( $blex_info->blocks as $block ) {

                if( $block->namespace == $namespace ) { 

                    $block_found = true;
                    $source = '';
                    $update_value = '';
                    $success_message = '';
                    $is_file_move = true;

                    switch( $args[1] ) {
                        case 'plugin-file':
                        case 'pf':
                            $basename = basename( $block->plugin_data->location );
                            $source = $this->_current_folder.$block->plugin_data->location;
                            $destination = $destination_directory.DIRECTORY_SEPARATOR.$basename;
                            $update_value = $blex_info->source_folder.DIRECTORY_SEPARATOR.$directory_arg;
                            $success_message = "The plugin file has been moved to the '".$directory_arg."' directory.";
                            break;
                        default: //destination is a directory
                            $source = $this->_current_folder.$block->container_directory;
                            $destination = $destination_directory;
                            $update_value = $blex_info->source_folder.DIRECTORY_SEPARATOR.$directory_arg;
                            $success_message = "The block has been moved to the '".$directory_arg."' directory.";
                            $is_file_move = false;
                            break;
                    }

                    if( file_exists( $destination_directory ) ){

                        if( '' !== $source ) {

                            if( !$this->destination_is_subdir( $source, $destination ) || $is_file_move ) {

                                rename( $source, $destination );
                                $plugin_php_content = file_get_contents( $this->_current_folder.'plugin.php' );

                                $current_location_directory = dirname( $block->plugin_data->location );

                                $plugin_php_content = str_replace( $current_location_directory, $update_value, $plugin_php_content );
                                file_put_contents( $this->_current_folder.'plugin.php' ,$plugin_php_content);
                                
                                if( is_dir( $destination ) ) {
                                    
                                    $block->registration_file =  str_replace( $block->container_directory, $update_value, $block->registration_file );
                                    $updated_styles = [];
                                    foreach( $block->styles as $style ) {
                                        $updated_styles[] = str_replace( $block->container_directory, $update_value, $style );
                                    }
                                    $block->styles = $updated_styles;
                                    $block->plugin_data->location = str_replace( $block->container_directory, $update_value, $block->plugin_data->location );
                                    

                                    //open the import file replace the container

                                    $current_block_directory = str_replace( $blex_info->source_folder, '.', $block->container_directory );
                                    $new_block_directory = str_replace( $blex_info->source_folder, '.', $update_value );

                                    $import_file_content = file_get_contents( $this->_current_folder.$blex_info->imports_file );
                                    $import_file_content = str_replace( $current_block_directory, $new_block_directory, $import_file_content );
                                    file_put_contents( $this->_current_folder.$blex_info->imports_file, $import_file_content );
                                    
                                    $block->container_directory = $update_value;

                                } else { 
                                    $block->plugin_data->location = str_replace( dirname( $block->plugin_data->location ), $update_value, $block->plugin_data->location );
                                }

                                $blex_info->write();

                                FoFo_Blex_Utils::success( $success_message );
                            } else {
                                FoFo_Blex_Utils::error( "The directory '".$directory_arg."' cannot be a sub directory of the block '".$namespace."' container directory.");
                            }
                        }

                    } else {
                        FoFo_Blex_Utils::error( "The directory '".$directory_arg."' was not found." );
                    }
                }
            }

            if( !$block_found ) {

                FoFo_Blex_Utils::warn( "The block with namespace '".$namespace."' was not found." );
            }
        });
    }

    private function destination_is_subdir( $source, $destination ) {

        $destination = dirname( $destination );
        if( false !== strpos( $destination, $source ) ) {
            return true;
        } 

        return false;
    }
}