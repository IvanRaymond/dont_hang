<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameParticipant;
use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class GameController extends AbstractController
{
    #[Route('/api/room/{roomId}/game/latest', name: 'app_game_latest', methods: ['GET'])]
    public function getLatestGame(int $roomId, EntityManagerInterface $entityManager): Response
    {
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if(!$room){
            return new Response('Room not found', 404);
        }
        $game = $entityManager->getRepository(Game::class)->findLatestByRoom($room->getId());
        if(!$game){
            return new Response('Game not found', 404);
        }
        $game->isActive();
        $json = $this->getSerializedGame($game);
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/api/room/{roomId}/game/data/{gameId}', name: 'app_game', methods: ['GET'])]
    public function getGame(int $roomId, int $gameId, EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->findOneByRoomAndGame($roomId, $gameId);
        $json = $this->getSerializedGame($game);
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Returns statistics for a given game
     */
    #[Route('/room/{roomId}/game/stats/{gameId}', name: 'app_game_stats')]
    public function getStatistics(int $roomId, int $gameId, EntityManagerInterface $entityManager): Response {
        // retrieve room
        $room = $entityManager->getRepository(Room::class)->find($roomId);

        // retrieve game
        $game = $entityManager->getRepository(Game::class)->find($gameId);

        // retrieve game participants
        $gameParticipants = $game->getGameParticipants();

        // retrieve game winners
        $gameWinners = $game->getGameWinners();

        // calculate average lose/win participants
        $totalParticipants = count($gameParticipants);
        $totalWinners = count($gameWinners);

        $averageLoseWin = ($totalWinners === 0) ? 0 : ($totalParticipants / $totalWinners);

        // get best player with most points
        $maxPoints = 0;
        $bestPlayer = null;

        foreach ($gameWinners as $winner) {
            $points = $winner->getPoints();

            if ($points > $maxPoints) {
                $maxPoints = $points;
                $bestPlayer = $winner->getUser();
            }
        }
        if ($bestPlayer !== null) {
            $bestPlayer = $bestPlayer->getUsername();
        }

        // retrieve game proposals of all participants
        $proposals = $game->getProposals();

        // calculate average proposals per participant
        $totalProposals = count($proposals);
        $averageProposalsPerParticipant = ($totalParticipants === 0) ? 0 : ($totalProposals / $totalParticipants);

        // build response data
        $data = [
            'averageLoseWin' => $averageLoseWin,
            'bestPlayer' => $bestPlayer,
            'averageProposalsPerParticipant' => $averageProposalsPerParticipant,
        ];

        $json = json_encode($data);

        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    #[Route('/api/room/{roomId}/game/create', name: 'app_game_create', methods: ['POST'])]
    public function startGame(Request $request, int $roomId, EntityManagerInterface $entityManager, HubInterface $hub): Response
    {
        // Check if there is already a game active
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if(!$room){
            return new Response('Room not found', 404);
        }
        $game = $entityManager->getRepository(Game::class)->findLatestByRoom($room->getId());
        if($game && $game->isActive()){
            return new Response('Game already active', 400);
        }
        // Check request for game duration, word
        $duration = $request->request->get('duration');
        $word = $request->request->get('word');
        $init = $request->request->get('word_initial_state');
        $classic = $request->request->get('mode');
        if(!$duration || !$word || !$init){
            return new Response('Missing parameters', 400);
        }
        // Create game
        $game = new Game();
        $game->setRoom($room);
        $game->setDuration($duration);
        $game->setWord($word);
        $game->setWordStatus($init);
        $game->setIsClassic($classic);
        $entityManager->persist($game);
        $entityManager->flush();
        // Add all room participants to game participants
        $roomParticipants = $room->getRoomParticipants();
        foreach ($roomParticipants as $roomParticipant) {
            $gameParticipant = new GameParticipant();
            $gameParticipant->setGame($game);
            $gameParticipant->setWordStatus($init);
            $gameParticipant->setUser($roomParticipant->getUser());
            $entityManager->persist($gameParticipant);
            $entityManager->flush();
        }
        // Push to all subscribers that game has started
        // Send game data
//        $update = new Update(
//            'http://localhost:8000/room/' . $roomId . '/game/watch',
//            $this->getSerializedGame($game),
//            false
//        );
//
////        $hub->publish($update);
//
//        // game loop
//        while($game->isActive()){
//            // Manage turns
//            // get list of all game participants
//            $gameParticipants = $game->getGameParticipants();
//            foreach ($gameParticipants as $gameParticipant) {
//                // Push to the gameParticipant that it is his turn and wait for response
//                // Let the player answer using the proposal endpoint
//                // Watch proposal endpoint for response ?
//                // Go to next player
//            }
//
//
//        }
        // return game result
        return new Response('Game created', 200);
    }

    #[Route('/api/room/{roomId}/game/end', name: 'app_game_end', methods: ['POST'])]
    public function endGame(int $roomId, EntityManagerInterface $entityManager): Response
    {
        // Check if there is already a game active
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        $game = $entityManager->getRepository(Game::class)->findLatestByRoom($room->getId());
        if (!$game) {
            return new Response('Game not found', 404);
        }
        // Check if game is already ended
        if (!$game->isActive()) {
            return new Response('Game already ended', 400);
        }
        // End game
        $game->setActive(false);
        $game->setWon(false);
        // TODO: Create trigger in DB to update finished_at when isWon is updated
        // Save game
        $entityManager->persist($game);
        $entityManager->flush();
        return new Response('Game ended', 200);
    }

    /**
     * @param mixed $game
     * @return string
     */
    private function getSerializedGame(Game $game): string
    {
        $activeGame = ['room', 'gameParticipants', 'proposals', 'word'];
        $finishedGame = ['room', 'gameParticipants'];
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($game, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            },
            'ignored_attributes' => $game->isActive() ? $activeGame : $finishedGame
        ]);
    }
}
