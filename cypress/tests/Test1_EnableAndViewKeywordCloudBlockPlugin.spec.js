const admin = Cypress.env('adminUser');
const adminPassword = Cypress.env('adminPassword');
const context = Cypress.env('context');

describe('Starts and View the block plugin', function() {
  it('Activate Plugin', function() {
    cy.login(admin, adminPassword, context);
    cy.get('.app__nav a')
      .contains('Website')
      .click();
    cy.get('button[id="plugins-button"]').click();
    cy.get('body').then(($body) => {
      if (
        !(
          $body.find(
            'tr[id="component-grid-settings-plugins-settingsplugingrid-category-blocks-row-keywordcloudblockplugin"] > :nth-child(3) > :nth-child(1) > :checked'
          ).length > 0
        )
      ) {
        cy.get(
          '#component-grid-settings-plugins-settingsplugingrid-category-blocks-row-keywordcloudblockplugin > :nth-child(3) >'
        ).click();
        cy.get(
          'div:contains(\'The plugin "Keyword Cloud Block" has been enabled.\')'
        );
      }
    });
    cy.logout();
  });

  it('Enable plugin in sidebar settings', function() {
    cy.login(admin, adminPassword, context);
    cy.get('.app__nav a')
      .contains('Website')
      .click();
    cy.get('#appearance-setup-button').click();
    cy.contains('Keyword Cloud Block')
      .parent()
      .find('input[type="checkbox"]')
      .check();
    cy.get(
      '#appearance-setup > .pkpForm > .pkpFormPages > .pkpFormPage > .pkpFormPage__footer > .pkpFormPage__buttons > .pkpButton'
    ).click();
    cy.logout();
  });

  it('Show the keyword cloud plugin in homepage', function() {
    cy.login(admin, adminPassword, context);
    cy.get('.app__contextTitle').click();
    cy.get('.block_Keywordcloud > .title').contains('Keywords');
  });
});
