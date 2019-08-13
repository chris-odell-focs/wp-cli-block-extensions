<?php

namespace FoFo_Blex;

class FoFo_Blex_Command {

	const CHAIN_LINK = '+';

	protected function execute( $args, $assoc_args, $callable ) {

		try {

			$this->do_chained_check( $args );
			$callable( $args, $assoc_args );
			$next_command = $this->get_next_command( $args );
			if( '' !== $next_command ) {
				\WP_CLI::runcommand( $next_command );
			}

		} catch( \Exception $e ) {

			\WP_CLI::error( $e->getMessage() );
		}
	}

	private function do_chained_check( $args ) {

		if( in_array( '+', $args ) && in_array( 'blex', $args ) ) {

			\WP_CLI::log( 'Command chain does not look correct' );
			\WP_CLI::log( "Should be $ wp blex <command> + 'wp blex <command>' + '..." );

			throw new \Exception( 'incorrect chaining format' );
		}
	}

	private function get_next_command( $args ) {

		$command_line = '';
		$next_chain_link = array_search( self::CHAIN_LINK, $args );
		if( false !== $next_chain_link ) {

			$commands = array_slice( $args, $next_chain_link + 1, count( $args ) - $next_chain_link + 1 );
			if( count( $commands ) && $commands[ count( $commands ) - 1 ] === self::CHAIN_LINK ) {
				array_splice( $commands, count( $commands ) - 1 );
			}
			
			$command_line = array_reduce( $commands, function( $acc, $item ){

				if( $acc !== '' ) {
					
					$acc.= $item === self::CHAIN_LINK ? ' '.self::CHAIN_LINK : " '".$item."'";

				} else {

					$tokens = explode( ' ', $item );
					$acc = implode( ' ', array_slice( $tokens, 1, count( $tokens ) - 1 ) );
				}

				return $acc;
				
			}, '');
		}

		return $command_line;
	}

	protected function check_for_blex_info( $current_folder ) {

		if( !file_exists( $current_folder.'/blex.info.json' ) ) {

			throw new FoFo_Blex_Command_Exception( "Could not find the 'blex.info.json' file which is required to continue." );
		}
	}

	protected function get_working_directory( $assoc_args ) {

		return ( isset( $assoc_args[ 'working_dir' ] ) ? $assoc_args[ 'working_dir' ] : getcwd() ).DIRECTORY_SEPARATOR;
	}

	protected function get_blex_info( $directory ) {
		
		return new FoFo_Blex_Info( $directory.'blex.info.json' );
	}
}
