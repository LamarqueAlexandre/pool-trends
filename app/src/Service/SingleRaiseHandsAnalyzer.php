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

    private array $groupsOfHands = [
        'suited broadways' => [
            'AKs',
            'AQs',
            'AJs',
            'ATs',
            'KQs',
            'KJs',
            'KTs',
            'QJs',
            'QTs',
            'JTs',
        ],
        'offsuit broadways' => [
            'AKo',
            'AQo',
            'AJo',
            'ATo',
            'KQo',
            'KJo',
            'KTo',
            'QJo',
            'QTo',
            'JTo',
        ],
        'premium' => [
            'AA',
            'KK',
            'QQ',
            'JJ',
            'TT',
        ],
        'middle pocket pairs' => [
            '99',
            '88',
            '77',
            '66',
        ],
        'low pocket pairs' => [
            '55',
            '44',
            '33',
            '22',
        ],
        'suited connectors' => [
            '32s',
            '43s',
            '54s',
            '65s',
            '76s',
            '87s',
            '98s',
            'T9s',
        ],
        'suited aces' => [
            'A2s',
            'A3s',
            'A4s',
            'A5s',
            'A6s',
            'A7s',
            'A8s',
            'A9s',
        ],
        'suited kings' => [
            'K2s',
            'K3s',
            'K4s',
            'K5s',
            'K6s',
            'K7s',
            'K8s',
            'K9s',
        ],
        'low offsuit aces' => [
            'A2o',
            'A3o',
            'A4o',
            'A5o',
        ],
        'middle offsuit aces' => [
            'A6o',
            'A7o',
            'A8o',
            'A9o',
        ],
        'low offsuit kings' => [
            'K2o',
            'K3o',
            'K4o',
            'K5o',
        ],
        'middle offsuit kings' => [
            'K6o',
            'K7o',
            'K8o',
            'K9o',
        ],
        'others types' => []
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

                    if (empty($this->stats[$spot][$position])) {
                        $this->stats[$spot][$position] = array_fill_keys(array_keys($this->groupsOfHands), []);
                    }

                    $type = $this->getHandGroup($this->groupsOfHands, $cards);

                    if (!array_key_exists($cards, $this->stats[$spot][$position][$type])) {
                        $this->stats[$spot][$position][$type][$cards] = 1;
                    } else {
                        $this->stats[$spot][$position][$type][$cards] += 1;
                    }
                }
            }
        }

        dd($this->stats);

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

    private function getHandGroup(array $mapping, string $hand)
    {
        foreach ($mapping as $type => $values) {
            if (in_array($hand, $values)) {
                return $type;
            }
        }

        return 'others types';
    }
}