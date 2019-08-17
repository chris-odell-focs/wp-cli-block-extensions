<?php

namespace FoFo_Blex;

class FoFo_Blex_Move extends FoFo_Blex_Command {

    private $_current_folder = null;
	private $_blex_info = null;

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