<?php

namespace App\Command;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// the name of the command is what users type after "php bin/console"
#[AsCommand(name: 'app:game-timer')]
class GameTimerCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Launches a timer for a specified amount of time.')
            ->addArgument('duration', InputArgument::REQUIRED, 'Duration in seconds')
            ->addArgument('gameId', InputArgument::REQUIRED, 'Game ID')
            ->setHelp('This command allows you to launch a timer.')
        ;
    }

    /**
     * Set game to inactive after the specified duration
     * If interrupted by SIGINT, set game to inactive and set duration to the time elapsed
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $duration = $input->getArgument('duration');
        $gameId = $input->getArgument('gameId');
        $game = $this->entityManager->getRepository(Game::class)->find($gameId);
        if(!$game || !$game->isActive()) {
            return Command::FAILURE;
        }
        sleep($duration);
        $game->setActive(false);
        $game->setFinishedIn($duration);
        $this->entityManager->persist($game);
        $this->entityManager->flush();
        return Command::SUCCESS;
    }
}