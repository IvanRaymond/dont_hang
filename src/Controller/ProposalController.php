<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameParticipant;
use App\Entity\Proposal;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProposalController extends AbstractController
{
    #[Route('/room/{roomId}/game/proposal', name: 'app_proposal_make')]
    public function create(int $roomId, Request $request, EntityManagerInterface $entityManager): Response
    {
        //Check if user is logged in
        if(!$this->getUser()){
            return new Response('Unauthorized', 401);
        }
        $user = $entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUserIdentifier());
        $room = $entityManager->getRepository(Room::class)->find($roomId);
        if(!$room){
            return new Response('Room not found', 404);
        }
        if($room->getOwner() === $user){
            return new Response('Game master cannot participate in game', 400);
        }
        $game = $entityManager->getRepository(Game::class)->findLatestByRoom($roomId);
        if(!$game){
            return new Response('Game not found', 404);
        }
        $gameParticipant = $entityManager->getRepository(GameParticipant::class)->findOneByGameAndUser($game->getId(), $user->getId());
        if(!$gameParticipant){
            return new Response('User not found in game', 404);
        }
        $proposition = $request->query->get('proposition');
        if(strlen($proposition) > 0) {
            $points = $this->getPoints($game->getWord(), $game->getWordStatus(), $proposition);
            $proposal = new Proposal();
            $proposal->setGame($game);
            $proposal->setUser($user);
            $proposal->setContent($proposition);
            $proposal->setCorrect($points > 0);
            $proposal->setPoints($points);
            $entityManager->persist($proposal);
            $entityManager->flush();
            $game->setWordStatus($this->updateWordStatus($game->getWord(), $game->getWordStatus(), $proposition));
            $entityManager->persist($game);
            $entityManager->flush();
            if($game->getWord() === $game->getWordStatus()) {
                $game->setWon(true);
                $game->setWinner($user);
                $game->setFinishedAt(new \DateTime());
                $entityManager->persist($game);
                $entityManager->flush();
                // TODO: Push to all subscribers that the game has been won

            }
            // Return points won and if game is won
            return new Response(json_encode([
                'points' => $points,
                'isWon' => $game->isWon()
            ]), 200, [
                'Content-Type' => 'application/json'
            ]);
        }
        // proposition is empty
        return new Response('Bad request', 400);
    }

   /**
    * Update the word status
    * @param string $word The word to complete
    * @param string $word_status The current status of the word
    * @param string $proposition The proposition to check
    * @return string
    */
    private function updateWordStatus(string $word, string $word_status, string $proposition): string
    {
        if ($proposition === $word) {
            $word_status = $proposition;
        } elseif (strlen($proposition) === 1) {
            $propositionLetter = $proposition[0];
            $word_statusArr = str_split($word_status);

            foreach ($word_statusArr as $index => $letter) {
                if ($letter === '_' && $word[$index] === $propositionLetter) {
                    $word_statusArr[$index] = $propositionLetter;
                }
            }

            $word_status = implode('', $word_statusArr);
        }
        return $word_status;
    }

    /**
     * Get the points won
     * @param string $word
     * @param string $word_status
     * @param string $proposition
     * @return int
     */
    private function getPoints(string $word, string $word_status, string $proposition): int
    {
        $wordArr = str_split($word);
        $wordStatusArr = str_split($word_status);
        $propositionArr = str_split($proposition);

        $discoveredLetters = [];

        if (count($propositionArr) === 1) {
            $letter = $propositionArr[0];
            $index = array_search($letter, $wordStatusArr);

            if ($index !== false && $wordStatusArr[$index] === '_') {
                $discoveredLetters[] = $letter;
            }
        } else {
            if ($proposition === $word) {
                foreach ($wordArr as $index => $letter) {
                    if ($wordStatusArr[$index] === '_') {
                        $discoveredLetters[] = $letter;
                    }
                }
            }
        }
        return count(array_unique($discoveredLetters));
    }
}
