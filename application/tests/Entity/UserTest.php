<?php
// tests/Entity/UserTest.php
namespace App\Tests\Entity;

use App\Entity\Subscription;
use App\Entity\User;
use App\Entity\File;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetterAndSetter()
    {
        // Création d'une instance des entités
        $user1 = new User();
        $user2 = new User();
        $subscription = new Subscription();
        $file = new File();

        // Définition de données de test
        $email1 = 'user1@test.com';
        $email2 = 'user2@test.com';
        $lastname = 'Doe';
        $firstname = 'John';
        $role = 'admin';

        $nameSubs = 'Premium Plan';
        $description = 'Accès illimité aux PDF';
        $maxPdf = 100;
        $price = 19.99;
        $specialPrice = 14.99;
        $specialPriceFrom = new \DateTime('2025-03-01');
        $specialPriceTo = new \DateTime('2025-03-31');

        $nameFile = 'test_file.pdf';
        $createdAt = new \DateTimeImmutable('2025-02-24');

        // Utilisation des setters pour les utilisateurs
        $user1->setEmail($email1);
        $user1->setLastname($lastname);
        $user1->setFirstname($firstname);
        $user1->setRole($role);
        $user1->setSubscription($subscription);

        $user2->setEmail($email2);
        $user2->setLastname($lastname);
        $user2->setFirstname($firstname);
        $user2->setRole($role);
        $user2->setSubscription($subscription);

        // Utilisation des setters pour la subscription
        $subscription->setName($nameSubs);
        $subscription->setDescription($description);
        $subscription->setMaxPdf($maxPdf);
        $subscription->setPrice($price);
        $subscription->setSpecialPrice($specialPrice);
        $subscription->setSpecialPriceFrom($specialPriceFrom);
        $subscription->setSpecialPriceTo($specialPriceTo);
        $subscription->addUser($user1);
        $subscription->addUser($user2);

        // Utilisation des setters pour le fichier
        $file->setName($nameFile);
        $file->setCreatedAt($createdAt);

        // Vérification des getters pour user1
        $this->assertEquals($email1, $user1->getEmail());
        $this->assertEquals($lastname, $user1->getLastname());
        $this->assertEquals($firstname, $user1->getFirstname());
        $this->assertEquals($role, $user1->getRole());
        $this->assertEquals($subscription, $user1->getSubscription());

        // Vérification des getters pour user2
        $this->assertEquals($email2, $user2->getEmail());
        $this->assertEquals($lastname, $user2->getLastname());
        $this->assertEquals($firstname, $user2->getFirstname());
        $this->assertEquals($role, $user2->getRole());
        $this->assertEquals($subscription, $user2->getSubscription());

        // Vérification des getters pour la subscription
        $this->assertEquals($nameSubs, $subscription->getName());
        $this->assertEquals($description, $subscription->getDescription());
        $this->assertEquals($maxPdf, $subscription->getMaxPdf());
        $this->assertEquals($price, $subscription->getPrice());
        $this->assertEquals($specialPrice, $subscription->getSpecialPrice());
        $this->assertEquals($specialPriceFrom, $subscription->getSpecialPriceFrom());
        $this->assertEquals($specialPriceTo, $subscription->getSpecialPriceTo());
        $users = $subscription->getUsers();
        $this->assertCount(2, $users);
        $this->assertContains($user1, $users);
        $this->assertContains($user2, $users);

        // Vérification des getters pour le fichier
        $this->assertEquals($nameFile, $file->getName());
        $this->assertEquals($createdAt, $file->getCreatedAt());
    }
}