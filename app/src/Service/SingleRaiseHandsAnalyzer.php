<?php

namespace App\Service;

class SingleRaiseHandsAnalyzer
{
    private array $spotsConfigurations = [];

    public function analyze(array $hands)
    {
        foreach ($hands as $hand) {
            $this->defineSituation($hand['Show Down'], $hand['Players Position']);
        }
        dump("ici");
        dump($hands);
        exit;
    }

    private function defineSituation(array $showdown, array $playersPositions)
    {
        $matchingRows = [];
        $positions = [];
        $spot = '';

        foreach ($playersPositions as $position => $pseudo) {
            if (array_key_exists($pseudo, $showdown)) {
                $matchingRows[$position] = $pseudo;
            }
        }

        $positions = array_keys($matchingRows);

        /**
         * Trouver un moyen de définir qui chaine de caractère qui précise le spot
         * 
         * Exemple : "Button vs SB vs BB"
         *           "Button vs BB"
         */
        if (!isset($this->spotsConfigurations[$positions[0] . ' vs ' . $positions[1]])) {
            dump($positions[0] . ' vs ' . $positions[1]);
            exit;
        }
        exit;
    }
}