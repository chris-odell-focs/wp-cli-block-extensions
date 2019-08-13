@rename
Feature: Run '$ wp blex rename'

  Scenario: Run rename without a working directory specified, and not running in plugin folder
    Given a WP install
    And a blex test block 'blex-test-block'

    When I try `wp blex rename block 'blex/block-blex-test-block' registration-file index.js`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  Scenario: Run rename when 'blex.info.json' is missing
    Given a WP install
    And a blex test block 'blex-test-block'
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file is missing

    When I try `wp blex rename block 'blex/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  Scenario: Rename a blocks registration file to 'index.js' with an incorrect namespace
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I try `wp blex rename block 'blex/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Warning: The block with namespace 'blex/block-blex-test-block' was not found
    """

  Scenario: Rename a blocks registration file to 'index.js' but the registration file cannot be found
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/block.js file is missing

    When I try `wp blex rename block 'cgb/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: The file 'src/block/block.js' could not be found.
    """

  Scenario: Rename a blocks registration file to 'index.js'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/index.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './block/index.js';
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    src\/block\/index.js
    """

  Scenario: Rename a blocks registration file to 'index.js' using the 'rf' alias
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' rf index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/index.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './block/index.js';
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    src\/block\/index.js
    """

 Scenario: Rename a blocks container directory to my-block but the container directory cannot be found
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/ directory is missing

    When I try `wp blex rename block 'cgb/block-blex-test-block' container-directory my-block --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: The directory 'src/block' could not be found.
    """

  Scenario: Rename a block directory called 'block' in the 'src/block' directory to 'my-block'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' container-directory my-block --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/my-block directory should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './my-block/block.js';
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "registration_file": "src\/my-block\/block.js"
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "container_directory": "src\/my-block",
    """

  Scenario: Rename a block directory called 'block' in the 'src/block' directory to 'my-block' using the cd alias
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' cd my-block --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/my-block directory should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './my-block/block.js';
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "registration_file": "src\/my-block\/block.js"
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "container_directory": "src\/my-block",
    """

  Scenario: Rename an 'imports' file in 'src' from 'blocks.js' to 'imports.js' on a non-ejected CGB block but blex.info does not exist
    Given a WP install
    And a blex test block 'blex-test-block'
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file is missing

    When I try `wp blex rename import-file imports.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  Scenario: Rename an 'imports' file in 'src' from 'blocks.js' to 'imports.js' on a non-ejected CGB block
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename import-file imports.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/imports.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/node_modules/cgb-scripts/config/paths.js file should contain:
    """
    pluginBlocksJs: resolvePlugin( 'src/imports.js' )
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "imports_file": "src\/imports.js",
    """

  Scenario: Rename an 'imports' file in 'src' from 'blocks.js' to 'imports.js' on an ejected CGB block
    Given a WP install
    And a blex test block 'blex-test-block-ejected'
    And a wp-content/plugins/blex-test-block-ejected/blex.info.json file:
    """
    {
        "info_version": "1.0",
        "template": "cgb-ejected",
        "config_directory": "config",
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

    When I run `wp blex rename import-file imports.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block-ejected`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/src/imports.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/config/paths.js file should contain:
    """
    pluginBlocksJs: resolvePlugin( 'src/imports.js' )
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block-ejected/blex.info.json file should contain:
    """
    "imports_file": "src\/imports.js",
    """

  Scenario: Rename block namespace 'cgb/block-blex-test-block' to 'blex/block-blex-test-block' but the belx.info.json file is missing
    Given a WP install
    And a blex test block 'blex-test-block'
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file is missing

    When I try `wp blex rename block 'cgb/block-blex-test-block' namespace 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: Could not find the 'blex.info.json' file which is required to continue.
    """

  Scenario: Rename block namespace 'foo/block-blex-test-block' to 'blex/block-blex-test-block' but the namespace 'foo/block-blex-test-block' cannot be found
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I try `wp blex rename block 'foo/block-blex-test-block' namespace 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Warning: The block with namespace 'foo/block-blex-test-block' was not found.
    """

  Scenario: Rename block namespace 'cgb/block-blex-test-block' to 'blex/block-blex-test-block' but the plugin file cannot be found
    Given a WP install
    And a blex test block 'blex-test-block'
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file is missing
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I try `wp blex rename block 'cgb/block-blex-test-block' namespace 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then STDERR should be:
    """
    Error: The plugin file 'src/init.php' could not be found.
    """

  Scenario: Rename block namespace 'cgb/block-blex-test-block' to 'blex/block-blex-test-block'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' namespace 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex/block-blex-test-block',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/block.js file should contain:
    """
    'blex/block-blex-test-block'
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "namespace": "blex\/block-blex-test-block",
    """

  Scenario: Rename namespace 'cgb/block-blex-test-block' to 'blex/block-blex-test-block' using the alias 'bns'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' bns 'blex/block-blex-test-block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex/block-blex-test-block',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/block.js file should contain:
    """
    'blex/block-blex-test-block'
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "namespace": "blex\/block-blex-test-block",
    """

  Scenario: Rename style slug 'blex_test_block-cgb-style-css' to 'blex_test_block-blex-style-css'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' style-slug 'blex_test_block-blex-style-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-style-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'style'         => 'blex_test_block-blex-style-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "style": "blex_test_block-blex-style-css",
    """
  
  Scenario: Rename style slug 'blex_test_block-cgb-style-css' to 'blex_test_block-blex-style-css' using the 'sts' alias
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' sts 'blex_test_block-blex-style-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-style-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'style'         => 'blex_test_block-blex-style-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "style": "blex_test_block-blex-style-css",
    """

  Scenario: Rename editor_script slug 'blex_test_block-cgb-block-js' to 'blex_test_block-blex-block-js'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' editor-script-slug 'blex_test_block-blex-block-js' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-block-js',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'editor_script' => 'blex_test_block-blex-block-js',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "editor_script": "blex_test_block-blex-block-js",
    """

  Scenario: Rename editor_script slug 'blex_test_block-cgb-block-js' to 'blex_test_block-blex-block-js' using alias 'eds'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' ess 'blex_test_block-blex-block-js' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-block-js',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'editor_script' => 'blex_test_block-blex-block-js',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "editor_script": "blex_test_block-blex-block-js",
    """

  Scenario: Rename editor_style slug 'blex_test_block-cgb-block-editor-css' to 'blex_test_block-blex-block-editor-css'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' editor-style-slug 'blex_test_block-blex-block-editor-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-block-editor-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'editor_style'  => 'blex_test_block-blex-block-editor-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "editor_style": "blex_test_block-blex-block-editor-css"
    """

  Scenario: Rename editor_style slug 'blex_test_block-cgb-block-editor-css' to 'blex_test_block-blex-block-editor-css' using the alias 'ests'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' ests 'blex_test_block-blex-block-editor-css' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    'blex_test_block-blex-block-editor-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain: 
    """
    'editor_style'  => 'blex_test_block-blex-block-editor-css',
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    "editor_style": "blex_test_block-blex-block-editor-css"
    """

  Scenario: Rename init hook blex_test_block_cgb_block_assets to blex_test_block_blex_block_assets
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' init-hook blex_test_block_blex_block_assets --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    function blex_test_block_blex_block_assets()
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    add_action( 'init', 'blex_test_block_blex_block_assets' );
    """

  Scenario: Rename init hook blex_test_block_cgb_block_assets to blex_test_block_blex_block_assets using the alias 'iho'
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rename block 'cgb/block-blex-test-block' iho blex_test_block_blex_block_assets --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    function blex_test_block_blex_block_assets()
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/init.php file should contain:
    """
    add_action( 'init', 'blex_test_block_blex_block_assets' );
    """

  Scenario: Rename a blocks registration file to 'index.js' using the 'rn' alias
    Given a WP install
    And a blex test block 'blex-test-block'
    And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

    When I run `wp blex rn block 'cgb/block-blex-test-block' registration-file index.js --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
    Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/index.js file should exist
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
    """
    import './block/index.js';
    """
    And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
    """
    src\/block\/index.js
    """