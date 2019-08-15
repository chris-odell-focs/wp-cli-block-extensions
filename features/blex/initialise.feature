@initialise
Feature: Run '$ wp blex initialise'

  Scenario: No template specified on a non-ejected block and no working_dir specified
    Given a WP install
    And a blex test block 'blex-test-block'

    When I try `wp blex initialise`
    Then STDERR should be:
    """
    Error: You do not appear to be in a plugin folder
    """

  Scenario: No template specified on a non-ejected block
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should exist

  Scenario: Uknown template specified on a non-ejected block
    Given a WP install
    And a blex test block 'blex-test-block'

    When I try `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block --template=foo`
    Then STDERR should be:
    """
    Error: The template 'foo' is not a recognised template and cannot be used
    """

  Scenario: 'cgb-ejected' template specified on an ejected block
    Given a WP install
    And a blex test block 'blex-test-block-ejected'

    When I run `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block-ejected --template=cgb-ejected`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/blex.info.json file should exist

  Scenario: No template specified on an ejected block
    Given a WP install
    And a blex test block 'blex-test-block-ejected'

    When I try `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block-ejected`
    Then STDERR should be:
    """
    Error: The 'config' folder does not exist but is expected for this template.
    """

  Scenario: Initialise run on the test block but plugin name does not match the expected pattern
    Given a WP install
    And a blex test block 'blex-test-block'
    And a wp-content/plugins/blex-test-block/plugin.php file:
    """
    <?php
    /**
    * Plugin Nome: Does_not_match_expected_pattern
    * Plugin URI: https://github.com/ahmadawais/create-guten-block/
    * Description: blex-test-block — is a Gutenberg plugin created via create-guten-block.
    * Author: mrahmadawais, maedahbatool
    * Author URI: https://AhmadAwais.com/
    * Version: 1.0.0
    * License: GPL2+
    * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
    *
    * @package CGB
    */

    // Exit if accessed directly.
    if ( ! defined( 'ABSPATH' ) ) {
      exit;
    }

    /**
    * Block Initializer.
    */
    require_once plugin_dir_path( __FILE__ ) . 'src/init.php';
    """

    When I try `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: The plugin name could not be found in the plugin.php file. Some blex functions may not work correctly.
    """

  @init_1
  Scenario: Initialise run on the test block
    Given a WP install
    And a blex test block 'blex-test-block'

    When I run `wp blex initialise --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    {
        "info_version": "1.0",
        "template": "cgb",
        "config_directory": "node_modules\/cgb-scripts\/config",
        "distributon_directory": "dist",
        "imports_file": "src\/blocks.js",
        "source_folder": "src",
        "blocks": [
            {
                "container_directory": "src\/block",
                "registration_file": "src\/block\/block.js",
                "styles": [
                    "src\/block\/editor.scss",
                    "src\/block\/style.scss"
                ],
                "namespace": "cgb\/block-blex-test-block",
                "plugin_data": {
                    "location": "src\/init.php",
                    "namespace": "cgb\/block-blex-test-block",
                    "style": "blex_test_block-cgb-style-css",
                    "editor_script": "blex_test_block-cgb-block-js",
                    "editor_style": "blex_test_block-cgb-block-editor-css",
                    "init_hook": "blex_test_block_cgb_block_assets"
                }
            }
        ]
    }
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
    """
    if( !defined( 'BLEX_TEST_BLOCK_SRC' ) ) {
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
    """
    define( 'BLEX_TEST_BLOCK_SRC', plugin_dir_path( __FILE__ ).'src'  );
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    plugins_url( 'dist/blocks.style.build.css', BLEX_TEST_BLOCK_SRC ), // Block style CSS.
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    plugins_url( '/dist/blocks.build.js', BLEX_TEST_BLOCK_SRC ), // Block.build.js: We register the block here. Built with Webpack.
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    plugins_url( 'dist/blocks.editor.build.css', BLEX_TEST_BLOCK_SRC ), // Block editor CSS.
    """
