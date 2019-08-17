@move
Feature: Run '$ wp blex move'

    Scenario: Run move without a working directory specified, and not running in plugin folder
        Given a WP install
        And a blex test block 'blex-test-block'

        When I try `wp blex move block 'cgb/block-blex-test-block' plugin-file 'foo'`
        Then STDERR should be:
        """
        Error: Could not find the 'blex.info.json' file which is required to continue.
        """

    Scenario: Move the block plugin registration file, but the destination directory cannot be found
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I try `wp blex move block 'cgb/block-blex-test-block' plugin-file 'foo' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then STDERR should be:
        """
        Error: The directory 'foo' was not found.
        """

    Scenario: Move a block plugin registration file, but the with an incorrect namespace
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I try `wp blex move block 'blex/block-blex-test-block' plugin-file 'block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then STDERR should be:
        """
        Warning: The block with namespace 'blex/block-blex-test-block' was not found.
        """
    
    Scenario: Move the block plugin registration file to a directory named 'block'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I run `wp blex move block 'cgb/block-blex-test-block' plugin-file 'block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/init.php file should exist
        And STDOUT should contain:
        """
        The plugin file has been moved to the 'block' directory.
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
        """
        'src/block/init.php'
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
        "location": "src\/block\/init.php",
        """

    Scenario: Move the block plugin registration file to a directory named 'block/subfolder'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file
        And a empty {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/subfolder directory

        When I run `wp blex move block 'cgb/block-blex-test-block' plugin-file 'block/subfolder' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/subfolder/init.php file should exist
        And STDOUT should contain:
        """
        The plugin file has been moved to the 'block/subfolder' directory
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
        """
        'src/block/subfolder/init.php'
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
        "location": "src\/block\/subfolder\/init.php",
        """

    Scenario: Move the block to a directory named 'block2'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file
        And a empty {RUN_DIR}/wp-content/plugins/blex-test-block/src/block2 directory

        When I run `wp blex move block 'cgb/block-blex-test-block' 'block2' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block2/block.js file should exist
        And STDOUT should contain:
        """
        The block has been moved to the 'block2' directory
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
        """
        import './block2/block.js';
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
                {
                    "container_directory": "src\/block2",
                    "registration_file": "src\/block2\/block.js",
                    "styles": [
                        "src\/block2\/editor.scss",
                        "src\/block2\/style.scss"
                    ],
                    "namespace": "cgb\/block-blex-test-block",
                    "plugin_data": {
                        "location": "src\/init.php",
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
        """
        require_once plugin_dir_path( __FILE__ ) . 'src/block2/init.php';
        """

    Scenario: Move the block to a directory named 'block2' where the plugin file has been moved to the target folder previously
        Given a WP install
        And a blex test block 'blex-test-block'
        And a wp-content/plugins/blex-test-block/blex.info.json file:
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
                        "location": "src\/block\/init.php",
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
        And a empty {RUN_DIR}/wp-content/plugins/blex-test-block/src/block2 directory

        When I run `wp blex move block 'cgb/block-blex-test-block' 'block2' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block2/block.js file should exist
        And STDOUT should contain:
        """
        The block has been moved to the 'block2' directory
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
        """
        import './block2/block.js';
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
                {
                    "container_directory": "src\/block2",
                    "registration_file": "src\/block2\/block.js",
                    "styles": [
                        "src\/block2\/editor.scss",
                        "src\/block2\/style.scss"
                    ],
                    "namespace": "cgb\/block-blex-test-block",
                    "plugin_data": {
                        "location": "src\/block2\/init.php",
        """

    Scenario: Move the block to a directory named 'allblocks/block2'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file
        And a empty {RUN_DIR}/wp-content/plugins/blex-test-block/src/allblocks/block2 directory

        When I run `wp blex move block 'cgb/block-blex-test-block' 'allblocks/block2' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/allblocks/block2/block.js file should exist
        And STDOUT should contain:
        """
        The block has been moved to the 'allblocks/block2' directory
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/src/blocks.js file should contain:
        """
        import './allblocks/block2/block.js';
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
                {
                    "container_directory": "src\/allblocks\/block2",
                    "registration_file": "src\/allblocks\/block2\/block.js",
                    "styles": [
                        "src\/allblocks\/block2\/editor.scss",
                        "src\/allblocks\/block2\/style.scss"
                    ],
        """

    Scenario: Move the block plugin registration file to a directory named 'block' using the alias 'mv'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I run `wp blex mv block 'cgb/block-blex-test-block' plugin-file 'block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/init.php file should exist
        And STDOUT should contain:
        """
        The plugin file has been moved to the 'block' directory.
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
        """
        'src/block/init.php'
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
        "location": "src\/block\/init.php",
        """
    
    Scenario: Move the block plugin registration file to a directory named 'block' using the alias 'pf'
        Given a WP install
        And a blex test block 'blex-test-block'
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I run `wp blex mv block 'cgb/block-blex-test-block' pf 'block' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        Then the {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/init.php file should exist
        And STDOUT should contain:
        """
        The plugin file has been moved to the 'block' directory.
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/plugin.php file should contain:
        """
        'src/block/init.php'
        """
        And the {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json file should contain:
        """
        "location": "src\/block\/init.php",
        """

    Scenario: Move the block to a directory named 'block/sub' and sub is a sub directory of the source directory
        Given a WP install
        And a blex test block 'blex-test-block'
        And a empty {RUN_DIR}/wp-content/plugins/blex-test-block/src/block/sub directory
        And a {RUN_DIR}/wp-content/plugins/blex-test-block/blex.info.json blex.info.json file

        When I try `wp blex move block 'cgb/block-blex-test-block' 'block/sub' --working_dir={RUN_DIR}/wp-content/plugins/blex-test-block`
        And STDERR should contain:
        """
        Error: The directory 'block/sub' cannot be a sub directory of the block 'cgb/block-blex-test-block' container directory.
        """
