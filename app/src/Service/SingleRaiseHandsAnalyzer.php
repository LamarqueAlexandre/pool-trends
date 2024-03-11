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
        '10',
        '9',
        '8',
        '7',
        '6',
        '5',
        '4',
        '3',
        '2'
    ];

    private array $families = ['c', 'd', 's', 'h'];

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
                    dump($position);
                    dump($cards);
                    exit;
                }
            }
            dump($hand);
            exit;
        }

        dump($this->spotsConfigurations);
        exit;
    }

    function getIndex($value, $array)
    {
        return array_search($value, $array);
    }

    private function formatCards(string $cards, array $cardsValues)
    {
        $cards = str_split($cards);
        dump($cards);
        usort($cards, function ($a, $b) use ($cardsValues) {
            $indexA = $this->getIndex($a, $cardsValues);
            $indexB = $this->getIndex($b, $cardsValues);
            return $indexA <=> $indexB;
        });

        dump($cards);
        exit;

        $cards = explode(' ', $cards);

        $family1 = substr($cards[0], -1);
        $family2 = substr($cards[1], -1);

        if ($family1 === $family2) {
            return $cards[0] . $cards[1] . "s";
        } else {
            return $cards[0] . $cards[1] . "o";
        }
        dd($cards);
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