Feature: Test that after executing '$ wp blex info' stdout contains "Current blex version"

  Scenario: After executing '$ wp blex info' stdout contains "Current blex version"
    Given a WP install

    When I run `wp blex info`
    Then STDOUT should contain:
      """
      Current blex version
      """
