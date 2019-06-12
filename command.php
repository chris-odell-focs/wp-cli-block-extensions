<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

require_once(dirname(__FILE__).'/commands/core/class-fofo-blex.php');
require_once(dirname(__FILE__).'/commands/core/class-fofo-blex-cgb.php');

WP_CLI::add_command( 'blex', new FoFo_Blex() );
WP_CLI::add_command( 'blex cgb', new FoFo_Blex_CGB() );
