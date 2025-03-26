describe('Inscription', () => {
    beforeEach(() => {
        cy.visit('http://symfony.mmi-troyes.fr:8319/register');
    });

    it('Réussit avec des informations valides', () => {
        cy.get('input[name="registrationForm[firstname]"]').type('TestPrenom1');
        cy.get('input[name="registrationForm[lastname]"]').type('TestNom');
        cy.get('input[name="registrationForm[email]"]').type('testeur@hotmail.com');
        cy.get('input[name="registrationForm[plainPassword]"]').type('Password123!');
        cy.get('input[name="registrationForm[agreeTerms]"]').check();
        cy.get('button[type="submit"]').click();

        cy.url().should('include', '/dashboard');
    });

    it('Échoue avec un compte déjà existant', () => {
        cy.get('input[name="registrationForm[firstname]"]').type('TestPrenom2');
        cy.get('input[name="registrationForm[lastname]"]').type('TestNom');
        cy.get('input[name="registrationForm[email]"]').type('testeur@hotmail.com');
        cy.get('input[name="registrationForm[plainPassword]"]').type('Password123!');
        cy.get('input[name="registrationForm[agreeTerms]"]').check();
        cy.get('button[type="submit"]').click();

        cy.get('.alert.alert-danger').should('exist');
    });
});
