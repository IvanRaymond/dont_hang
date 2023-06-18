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

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:create-game-master')]
class CreateGameMasterCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new Game Master user.')
            ->setHelp('This command allows you to create a new Game Master user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');

        $question = new Question('Enter the username: ');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Enter the email: ');
        $email = $helper->ask($input, $output, $question);

        $question = new Question('Enter the password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $plainPassword = $helper->ask($input, $output, $question);

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );
        $user->setMaster(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('Game Master created successfully.');

        return Command::SUCCESS;
    }
}