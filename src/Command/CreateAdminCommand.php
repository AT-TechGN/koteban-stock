<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Crée un utilisateur administrateur'
)]
class CreateAdminCommand extends Command
{
    private $entityManager;
    private $passwordHasher;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->validator = $validator;
    }

    protected function configure(): void
    {
        $this->setHelp('Cette commande permet de créer un utilisateur administrateur avec le rôle ROLE_ADMIN.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        // Demander le username avec validation
        $username = $this->askValidatedInput(
            $helper,
            $input,
            $output,
            new Question('Entrez le nom d\'utilisateur : '),
            function ($value) {
                if (empty($value)) {
                    throw new \RuntimeException('Le nom d\'utilisateur ne peut pas être vide.');
                }
                return $value;
            }
        );

        // Demander l'email avec validation
        $email = $this->askValidatedInput(
            $helper,
            $input,
            $output,
            new Question('Entrez l\'email : '),
            function ($value) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Veuillez entrer une adresse email valide.');
                }
                return $value;
            }
        );

        // Demander le mot de passe avec validation
        $password = $this->askValidatedInput(
            $helper,
            $input,
            $output,
            (new Question('Entrez le mot de passe : '))
                ->setHidden(true)
                ->setHiddenFallback(false),
            function ($value) {
                if (strlen($value) < 6) {
                    throw new \RuntimeException('Le mot de passe doit contenir au moins 6 caractères.');
                }
                return $value;
            }
        );

        // Vérifier si l'utilisateur existe déjà
        if ($this->userExists($username, $email)) {
            $output->writeln('<error>Un utilisateur avec ce nom d\'utilisateur ou cet email existe déjà.</error>');
            return Command::FAILURE;
        }

        // Créer et valider l'utilisateur
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setCreatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $output->writeln('<error>Erreurs de validation :</error>');
            foreach ($errors as $error) {
                $output->writeln(sprintf(' - %s', $error->getMessage()));
            }
            return Command::FAILURE;
        }

        // Enregistrer l'utilisateur
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln([
            '<info>Utilisateur administrateur créé avec succès !</info>',
            sprintf('Nom d\'utilisateur: <comment>%s</comment>', $username),
            sprintf('Email: <comment>%s</comment>', $email),
            'Rôle: <comment>ROLE_ADMIN</comment>',
        ]);

        return Command::SUCCESS;
    }

    private function askValidatedInput($helper, $input, $output, Question $question, callable $validator)
    {
        $question->setValidator($validator);
        return $helper->ask($input, $output, $question);
    }

    private function userExists(string $username, string $email): bool
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]) !== null
            || $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]) !== null;
    }
}