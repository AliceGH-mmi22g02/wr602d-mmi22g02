describe('Génération de PDF', () => {
    beforeEach(() => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/generate-pdf');
    });

    it('Réussit avec un fichier HTML valide', () => {
        const filePath = 'cypress/fixtures/valid-file.html';
        cy.get('input[name="form[htmlFile]"]').selectFile(filePath, { force: true });
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/pdf-generated'); // Vérifie que l'utilisateur est redirigé après génération
    });

    it('Échoue avec un fichier non HTML', () => {
        const filePath = 'cypress/fixtures/invalid-file.txt';
        cy.get('input[name="form[htmlFile]"]').selectFile(filePath, { force: true });
        cy.get('button[type="submit"]').click();

        cy.get('.alert.alert-danger').should('exist'); // Vérifie qu'un message d'erreur s'affiche
    });
});