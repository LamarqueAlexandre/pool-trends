<?php

namespace App\Service;

class SingleRaiseHandsAnalyzer
{
    private array $spotsConfigurations = [];

    private array $cards = [
        'A',
        'K',
        'Q',
        'J',
        'T',
        '9',
        '8',
        '7',
        '6',
        '5',
        '4',
        '3',
        '2'
    ];

    private array $stats = [];

    public function analyze(array $hands)
    {
        foreach ($hands as $id => $hand) {
            try {
                $this->getDataFromSpot($hand, $id);

            } catch (\Exception $e) {
                dump($id);
            }
        }

        foreach ($this->spotsConfigurations as $spot => $hands) {
            foreach ($hands as $idHand => $positions) {
                foreach ($positions as $position => $cards) {
                    $cards = $this->formatCards($cards, $this->cards);
                    $this->spotsConfigurations[$spot][$idHand][$position] = $cards;
                }
            }
        }

        foreach ($this->spotsConfigurations as $spot => $hands) {

            if (!in_array($spot, $this->stats)) {
                $this->stats[$spot] = [];
            }

            foreach ($hands as $hand) {
                foreach ($hand as $position => $cards) {
                    if (!array_key_exists($position, $this->stats[$spot])) {
                        $this->stats[$spot][$position] = [];
                    }

                    if (!array_key_exists($cards, $this->stats[$spot][$position])) {
                        $this->stats[$spot][$position][$cards] = 1;
                    } else {
                        $this->stats[$spot][$position][$cards] += 1;
                    }
                }
            }
        }

        foreach ($this->stats as $spot => $positions) {
            foreach ($positions as $position => $hands) {
                $total = array_sum($hands);
                foreach ($hands as $hand => $value) {
                    $this->stats[$spot][$position][$hand] = round(($value / $total) * 100, 4);
                }
                arsort($this->stats[$spot][$position]);
            }
        }

        dump($this->stats);
        exit;
    }

    private function getIndex($value, $array)
    {
        return array_search($value, $array);
    }

    private function formatCards(string $cards, array $cardsValues)
    {
        $explodedCards = explode(' ', $cards);

        $family1 = substr($explodedCards[0], -1);
        $family2 = substr($explodedCards[1], -1);

        $suited = $family1 === $family2 ? 's' : 'o';

        $explodedCards[0] = substr($explodedCards[0], 0, 1);
        $explodedCards[1] = substr($explodedCards[1], 0, 1);
        
        if ($explodedCards[0] === $explodedCards[1]) {
            return $explodedCards[0] . $explodedCards[1];
        }

        usort($explodedCards, function ($a, $b) use ($cardsValues) {
            $indexA = $this->getIndex($a, $cardsValues);
            $indexB = $this->getIndex($b, $cardsValues);
            return $indexA <=> $indexB;
        });

        return $explodedCards[0] . $explodedCards[1] . $suited;
    }

    private function getDataFromSpot(array $hand, string $idHand)
    {
        $matchingRows = [];

        foreach ($hand['Players Position'] as $position => $pseudo) {
            if (array_key_exists($pseudo, $hand['Show Down'])) {
                $matchingRows[$position] = $pseudo;
            }
        }

        /**
         * Trouver un moyen de définir qui chaine de caractère qui précise le spot
         * 
         * Exemple : "Button vs SB vs BB"
         *           "Button vs BB"
         */
        $configuration = implode(" vs ", array_keys($matchingRows));

        if (!isset($this->spotsConfigurations[$configuration])) {
            $this->spotsConfigurations[$configuration] = [];
        }

        $playersPositionsShowdown = [];
        
        foreach ($hand['Players Position'] as $position => $pseudo) {
            if (array_key_exists($pseudo, $hand['Show Down'])) {
                $playersPositionsShowdown[$position] = $hand['Show Down'][$pseudo];
            }
        }

        $this->spotsConfigurations[$configuration][$idHand] = $playersPositionsShowdown;
    }
}