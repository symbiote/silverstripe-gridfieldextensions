Feature: Using GridFieldConfigurablePaginator in place of GridFieldPaginator
As a content editor
I want to use the functionality in GridFieldConfigurablePaginator

  Background:
    Given I add an extension "Symbiote\GridFieldExtensions\Tests\Stub\Extension\ConfigurablePaginatorExtension" to the "SilverStripe\FrameworkTest\Model\Company" class without dev-build
    And I add an extension "Symbiote\GridFieldExtensions\Tests\Stub\Extension\NoCoworkersExtension" to the "SilverStripe\FrameworkTest\Model\Employee" class without dev-build
    And there are the following SilverStripe\FrameworkTest\Model\Employee records
    """
    employee1:
      Name: "Adam"
    employee2:
      Name: "Jim"
    employee3:
      Name: "Tom"
    employee4:
      Name: "James"
    employee5:
      Name: "Jane"
    employee6:
      Name: "Chloe"
    employee7:
      Name: "Amy"
    employee8:
      Name: "Sarah"
    employee9:
      Name: "Sam"
    employee10:
      Name: "Ashleigh"
    employee11:
      Name: "Tiago"
    employee12:
      Name: "Catalina"
    employee13:
      Name: "Oscar"
    employee14:
      Name: "Lima"
    employee15:
      Name: "Danie"
    employee16:
      Name: "Lucia"
    """
    Given there are the following SilverStripe\FrameworkTest\Model\Company records
    """
    company-1:
      Name: "Company One"
      Employees:
        - =>SilverStripe\FrameworkTest\Model\Employee.employee1
        - =>SilverStripe\FrameworkTest\Model\Employee.employee2
        - =>SilverStripe\FrameworkTest\Model\Employee.employee3
        - =>SilverStripe\FrameworkTest\Model\Employee.employee4
        - =>SilverStripe\FrameworkTest\Model\Employee.employee5
        - =>SilverStripe\FrameworkTest\Model\Employee.employee6
        - =>SilverStripe\FrameworkTest\Model\Employee.employee7
        - =>SilverStripe\FrameworkTest\Model\Employee.employee8
        - =>SilverStripe\FrameworkTest\Model\Employee.employee9
        - =>SilverStripe\FrameworkTest\Model\Employee.employee10
        - =>SilverStripe\FrameworkTest\Model\Employee.employee11
        - =>SilverStripe\FrameworkTest\Model\Employee.employee12
        - =>SilverStripe\FrameworkTest\Model\Employee.employee13
        - =>SilverStripe\FrameworkTest\Model\Employee.employee14
        - =>SilverStripe\FrameworkTest\Model\Employee.employee15
        - =>SilverStripe\FrameworkTest\Model\Employee.employee16
    """
    And the "group" "EDITOR" has permissions "Access to 'Pages' section" and "Access to 'GridField Test Navigation' section" and "TEST_DATAOBJECT_EDIT"
    And I am logged in as a member of "EDITOR" group
    And I go to "/admin/gridfield-test-navigation"
    And I click "Company One" in the "#Form_EditForm" element
    And I click the "Employees" CMS tab

  Scenario: I can navigate through a paginated gridfield
    When I should see "Company One" in the ".breadcrumbs-wrapper" element
    # Check we have the first and last expected values for page 1
    Then I should see "Adam" in the "#Form_ItemEditForm" element
    And I should see "Danie" in the "#Form_ItemEditForm" element
    # Check we don't have the first expected value for page 2
    And I should not see "Lucia" in the "#Form_ItemEditForm" element
    # Check the paginator elements render correctly
    And I should see "1–15 of 16" in the ".grid-field__paginator_numbers" element
    And the ".pagination-page-number" element should contain "<input class=\"text no-change-track\" value=\"1\" data-skip-autofocus=\"true\">"
    And I should see "15" in the ".pagination-page-size-select" element
    # Go to page 2 using buttons
    Given I press the "Next" button
    Then I should not see "Adam" in the "#Form_ItemEditForm" element
    And I should not see "Danie" in the "#Form_ItemEditForm" element
    And I should see "Lucia" in the "#Form_ItemEditForm" element
    And I should see "16–16 of 16" in the ".grid-field__paginator_numbers" element
    And the ".pagination-page-number" element should contain "<input class=\"text no-change-track\" value=\"2\" data-skip-autofocus=\"true\">"
    # Go to page 1 using the page number input field
    Given I click on the ".pagination-page-number input" element
    And I press the "Backspace" key globally
    And I type "1" in the field
    And I press the "Enter" key globally
    Then I should see "Adam" in the "#Form_ItemEditForm" element
    And I should see "Danie" in the "#Form_ItemEditForm" element
    And I should not see "Lucia" in the "#Form_ItemEditForm" element
    And I should see "1–15 of 16" in the ".grid-field__paginator_numbers" element
    And the ".pagination-page-number" element should contain "<input class=\"text no-change-track\" value=\"1\" data-skip-autofocus=\"true\">"
    And I should see "15" in the ".pagination-page-size-select" element
