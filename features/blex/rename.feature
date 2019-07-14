Feature: Run '$ wp blex rename'

  Scenario: Run rename without a working directory specified, and not running in plugin folder
    Given a WP install
    And a blex test block 'blex-test-block'

    When I try `wp blex rename block block.js index.js`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  Scenario: Run rename when 'blex.info.json' is missing
    Given a WP install
    And a blex test block 'blex-test-block'
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file is missing

    When I try `wp blex rename block block.js index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  @rename
  Scenario: Rename a block file called 'block.js' in the 'src/block' directory to 'index.js'
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex rename block block.js index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/index.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './block/index.js';
    """

  Scenario: Rename a block directory called 'block' in the 'src/block' directory to 'my-block'
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex rename block block my-block --path={RUN_DIR}`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/my-block directory should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain
    """
    import './my-block/block.js';
    """

  Scenario: Rename an 'imports' file in 'src' from 'blocks.js' to 'imports.js' on a non-ejected CGB block
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex rename imports blocks.js imports.js --path={RUN_DIR}`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/imports.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/node_modules/cgb-scripts/config/path.js file should contain
    """
    pluginBlocksJs: resolvePlugin( 'src/imports.js' )
    """

  Scenario: Rename an 'imports' file in 'src' from 'blocks.js' to 'imports.js' on an ejected CGB block
    Given a WP install
    And a blex test block 'blex-test-block-ejected'

    When I run `wp blex rename imports blocks.js imports.js --path={RUN_DIR}`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/src/imports.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/config/path.js file should contain
    """
    pluginBlocksJs: resolvePlugin( 'src/imports.js' )
    """

  Scenario: Rename namespace 'cgb/block-blex-test-block' to 'blex/block-blex-test-block'
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex rename namespace 'cgb/block-blex-test-block' 'blex/block-blex-test-block' --path={RUN_DIR}`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain
    """
    wp_register_style(
		    'blex_test_block-blex-style-css', // Handle.
		    plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), // Block style CSS.
		    array( 'wp-editor' ), // Dependency to include the CSS after it.
		    null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' ) // Version: File modification time.
	    );

	    // Register block editor script for backend.
	    wp_register_script(
		    'blex_test_block-blex-block-js', // Handle.
		    plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ), // Block.build.js: We register the block here. Built with Webpack.
		    array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ), // Dependencies, defined above.
		    null, // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
		    true // Enqueue the script in the footer.
	    );

	    // Register block editor styles for backend.
	    wp_register_style(
		    'blex_test_block-blex-block-editor-css', // Handle.
		    plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
		    array( 'wp-edit-blocks' ), // Dependency to include the CSS after it.
		    null // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' ) // Version: File modification time.
	    );

	    /**
	     * Register Gutenberg block on server-side.
	     *
	     * Register the block on server-side to ensure that the block
	     * scripts and styles for both frontend and backend are
	     * enqueued when the editor loads.
	     *
	     * @link https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
	     * @since 1.16.0
	     */
	    register_block_type(
		    'blex/block-blex-test-block', array(
			    // Enqueue blocks.style.build.css on both frontend & backend.
			    'style'         => 'blex_test_block-blex-style-css',
			    // Enqueue blocks.build.js in the editor only.
			    'editor_script' => 'blex_test_block-blex-block-js',
			    // Enqueue blocks.editor.build.css in the editor only.
			    'editor_style'  => 'blex_test_block-blex-block-editor-css',
		    )
	    );
    """
    And the file {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/block.js should contain
    """
    'blex/block-blex-test-block'
    """

  Scenario: Rename init hook blex_test_block_cgb_block_assets to blex_test_block_blex_block_assets
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex rename inithook blex_test_block_cgb_block_assets blex_test_block_blex_block_assets --path={RUN_DIR}`
    Then the file {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php should contain
    """
    function blex_test_block_cgb_block_assets()
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain
    """
    add_action( 'init', 'blex_test_block_cgb_block_assets' );
    """
