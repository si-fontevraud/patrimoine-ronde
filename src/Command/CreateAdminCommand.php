<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Crée un compte administrateur de base pour la plateforme.')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail de l’administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe administrateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = strtolower(trim((string) $input->getArgument('email')));
        $password = (string) $input->getArgument('password');

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln(sprintf('<error>Un utilisateur avec l’adresse %s existe déjà.</error>', $email));

            return Command::FAILURE;
        }

        $user = new User();
        $user
            ->setEmail($email)
            ->setFirstName('Admin')
            ->setLastName('Ronde')
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER'])
            ->setIsActive(true)
            ->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('<info>Compte admin créé : %s</info>', $email));

        return Command::SUCCESS;
    }
}
