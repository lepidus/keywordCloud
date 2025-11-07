
describe('Keyword Cloud - Enable plugin and add it to the sidebar', function() {
    it('Enable plugin', function() {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Website').click({ force: true });

		cy.waitJQuery();
        cy.get('button[id="plugins-button"]').click();
        cy.get('input[id^=select-cell-keywordcloudblockplugin]').check();
		cy.get('input[id^=select-cell-keywordcloudblockplugin]').should('be.checked');
    });
    it('Add plugin to sidebar', function() {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.get('nav').contains('Settings').click();
        cy.get('nav').contains('Website').click({ force: true });        
        cy.waitJQuery();
        cy.get('button[id="appearance-button"]').click();
        cy.get('#appearance-setup-button').click();
        cy.get('input[value="keywordcloudblockplugin"]').check();
        cy.get('button:contains("Save"):visible').click();
    });
    it('Check presence of keyword cloud block in public site', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.contains('Journal of Public Knowledge').click();
        cy.get('.block_keyword_cloud');
    });
});