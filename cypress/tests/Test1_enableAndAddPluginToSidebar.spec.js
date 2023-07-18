describe('Keyword Cloud - Enable plugin and add it to the sidebar', function() {
    it('Enable Plugin', function() {
        cy.login('dbarnes', null, 'publicknowledge');

        cy.get('a:contains("Website")').click();

		cy.waitJQuery();
		cy.get('#plugins-button').click();        
        cy.get('input[id^=select-cell-keywordcloudblockplugin]').check();
		cy.get('input[id^=select-cell-keywordcloudblockplugin]').should('be.checked');
    });
    it('Add plugin to sidebar', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('a:contains("Website")').click();
        
        cy.waitJQuery();
		cy.get('#appearance-button').click();
        cy.get('#appearance-setup-button').click();
        cy.get('input[value="keywordcloudblockplugin"]').check();
        cy.get('button:contains("Save"):visible').click();
    });
    it('Check presence of keyword cloud block in public site', function() {
        cy.login('dbarnes', null, 'publicknowledge');
        cy.get('.app__contextTitle').click();
        cy.get('.block_Keywordcloud > .title').contains('Keywords');    
    });
});
