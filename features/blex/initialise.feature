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
