
describe('Keyword Cloud - Enable plugin and add it to the sidebar', function() {
    it('Enable plugin', function() {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.get('a:contains("Website")').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();        
        cy.get('input[id^=select-cell-keywordcloudplugin]').check();
		cy.get('input[id^=select-cell-keywordcloudplugin]').should('be.checked');
    });
    it('Add plugin to sidebar', function() {
        cy.get('a:contains("Website")').click();
        
        cy.waitJQuery();
		cy.get('#appearance-button').click();
        cy.get('#appearance-setup-button').click();
        cy.get('input[value="keywordcloudblockplugin"]').click();
        cy.get('#appearance-setup form').get('button:contains("Save")').click();
    });
});