<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Subscription;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class AppFixtures extends Fixture
{
    private Generator $faker;

    // Injection de Faker dans le constructeur
    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function load(ObjectManager $manager): void
    {
        // Crée 3 abonnements liés à la génération et la conversion de PDFs
        $subscriptions = [
            [
                'name' => 'Free',
                'description' => 'Accès limité à 1 PDFs par mois.',
                'maxPdf' => 1,
                'price' => 0.00,
                'specialPrice' => 0.00,
                'specialPriceFrom' => new DateTimeImmutable(),
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'),
            ],
            [
                'name' => 'Basic',
                'description' => 'Accès limité à 3 PDFs par mois. Conversion basique.',
                'maxPdf' => 3,
                'price' => 1.99,
                'specialPrice' => 0.99,
                'specialPriceFrom' => new DateTimeImmutable(),
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'),
            ],
            [
                'name' => 'Premium',
                'description' => 'Conversion de 6 PDFs par mois et des options avancées.',
                'maxPdf' => 6,
                'price' => 2.99,
                'specialPrice' => 1.99,
                'specialPriceFrom' => new DateTimeImmutable(),
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'),
            ],
            [
                'name' => 'Pro',
                'description' => 'Solution complète de conversion de PDFs pour entreprises, 
                avec des fonctionnalités personnalisées.',
                'maxPdf' => 9,
                'price' => 3.99,
                'specialPrice' => 2.99,
                'specialPriceFrom' => new DateTimeImmutable(),
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'),
            ],
        ];

        foreach ($subscriptions as $data) {
            $subscription = new Subscription();
            $subscription->setName($data['name']);
            $subscription->setDescription($data['description']);
            $subscription->setMaxPdf($data['maxPdf']);
            $subscription->setPrice($data['price']);
            $subscription->setSpecialPrice($data['specialPrice']);
            $subscription->setSpecialPriceFrom($data['specialPriceFrom']);
            $subscription->setSpecialPriceTo($data['specialPriceTo']);
            $manager->persist($subscription);

            // Crée des utilisateurs pour chaque abonnement
            for ($i = 1; $i <= 3; $i++) {
                $user = new User();
                $user->setEmail($this->faker->email);
                $user->setLastname($this->faker->lastName);
                $user->setFirstname($this->faker->firstName);
                $user->setPassword(password_hash('password', PASSWORD_BCRYPT)); // Ajoute un mot de passe chiffré
                $user->setRoles(['ROLE_USER']);
                $user->setSubscription($subscription);
                $user->setIsVerified(true);  // Marque l'utilisateur comme vérifié
                $manager->persist($user);

                // Crée des fichiers associés à l'utilisateur
                $numFiles = rand(1, 3); // Chaque utilisateur aura entre 1 et 3 fichiers
                for ($j = 0; $j < $numFiles; $j++) {
                    $file = new File();
                    $file->setName($this->faker->word . '.pdf');
                    $file->setCreatedAt((new DateTimeImmutable())->modify('now'));

                    // Lier le fichier à l'utilisateur
                    $file->setUser($user);

                    $manager->persist($file);
                }
            }
        }

        $manager->flush(); // Enregistre toutes les entités persistées en base de données
    }
}
