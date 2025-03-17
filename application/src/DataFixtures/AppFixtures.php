<?php
namespace App\DataFixtures;

use App\Entity\File;
use App\Entity\Subscription;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create(); // Instanciation manuelle de Faker

        // Crée 3 abonnements liés à la génération et la conversion de PDFs
        $subscriptions = [
            [
                'name' => 'Free',
                'description' => 'Accès limité à 1 PDFs par mois.',
                'maxPdf' => 1,
                'price' => 0.00,
                'specialPrice' => 0.00, // Pas de prix spécial, on met le prix standard
                'specialPriceFrom' => new DateTimeImmutable(), // Date actuelle
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'), // 30 jours à partir de maintenant
            ],
            [
                'name' => 'Basic',
                'description' => 'Accès limité à 3 PDFs par mois. Conversion basique.',
                'maxPdf' => 3,
                'price' => 1.99,
                'specialPrice' => 0.99, // Pas de prix spécial, on met le prix standard
                'specialPriceFrom' => new DateTimeImmutable(), // Date actuelle
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'), // 30 jours à partir de maintenant
            ],
            [
                'name' => 'Premium',
                'description' => 'Conversion de 6 PDFs par mois et des options avancées.',
                'maxPdf' => 6,
                'price' => 2.99,
                'specialPrice' => 1.99, // Prix spécial
                'specialPriceFrom' => new DateTimeImmutable(), // Date actuelle
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'), // 30 jours à partir de maintenant
            ],
            [
                'name' => 'Pro',
                'description' => 'Solution complète de conversion de PDFs pour entreprises, avec des fonctionnalités personnalisées.',
                'maxPdf' => 9,
                'price' => 3.99,
                'specialPrice' => 2.99, // Pas de prix spécial
                'specialPriceFrom' => new DateTimeImmutable(), // Date actuelle
                'specialPriceTo' => (new DateTimeImmutable())->modify('+30 days'), // 30 jours à partir de maintenant
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
                $user->setEmail($faker->email);
                $user->setLastname($faker->lastName);
                $user->setFirstname($faker->firstName);
                $user->setPassword(password_hash('password', PASSWORD_BCRYPT)); // Ajoute un mot de passe chiffré
                $user->setRoles(['ROLE_USER']);
                $user->setSubscription($subscription);
                $user->setIsVerified(true);  // Marque l'utilisateur comme vérifié
                $manager->persist($user);

                // Crée des fichiers associés à l'utilisateur
                $numFiles = rand(1, 3); // Chaque utilisateur aura entre 1 et 3 fichiers
                for ($j = 0; $j < $numFiles; $j++) {
                    $file = new File();
                    $file->setName($faker->word . '.pdf');
                    $file->setCreatedAt((new DateTimeImmutable())->modify('now')); // Utilisation de \DateTimeImmutable

                    // Lier le fichier à l'utilisateur via l'ID de l'utilisateur
                    $file->setUser($user); // Cela associe le fichier à l'utilisateur

                    $manager->persist($file); // Sauvegarde le fichier
                }
            }
        }

        $manager->flush(); // Enregistre toutes les entités persistées en base de données
    }
}
