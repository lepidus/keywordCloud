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

        cy.visit('');
        cy.get('.block_Keywordcloud > .title').contains('Keywords'); 
    });
    it('Load the cloud libraries bundled with the plugin', function() {
        cy.visit('');
        cy.get('script[src*="keywordCloud/js/vendor/d3/d3.min.js"]').should('exist');
        cy.get('script[src*="keywordCloud/js/vendor/d3-cloud/d3.layout.cloud.js"]').should('exist');
        cy.get('script[src*="cdn.jsdelivr.net"], script[src*="d3js.org"]').should('not.exist');
        cy.window().its('d3.layout.cloud').should('be.a', 'function');
    });
});
