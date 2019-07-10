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
