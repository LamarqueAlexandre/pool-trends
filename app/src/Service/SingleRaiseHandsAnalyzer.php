<?php

namespace App\Service;

class SingleRaiseHandsAnalyzer
{
    private array $spotsConfigurations = [];

    public function analyze(array $hands)
    {
        foreach ($hands as $id => $hand) {
            try {
                $this->getDataFromSpot($hand['Show Down'], $hand['Players Position']);

            } catch (\Exception $e) {
                dump($id);
            }
        }

        dump($this->spotsConfigurations);
        exit;
    }

    private function getDataFromSpot(array $showdown, array $playersPositions)
    {
        $matchingRows = [];

        foreach ($playersPositions as $position => $pseudo) {
            if (array_key_exists($pseudo, $showdown)) {
                $matchingRows[$position] = $pseudo;
            }
        }

        /**
         * Trouver un moyen de définir qui chaine de caractère qui précise le spot
         * 
         * Exemple : "Button vs SB vs BB"
         *           "Button vs BB"
         */
        $configuration = implode(" vs ", array_reverse(array_keys($matchingRows)));

        if (!isset($this->spotsConfigurations[$configuration])) {
            $this->spotsConfigurations[$configuration] = [];
        }

        $playersPositionsShowdown = [];
        
        foreach ($playersPositions as $position => $pseudo) {
            if (array_key_exists($pseudo, $showdown)) {
                $playersPositionsShowdown[$position] = $showdown[$pseudo];
            }
        }

        $playersPositionsShowdown = array_reverse($playersPositionsShowdown);

        $this->spotsConfigurations[$configuration][] = $playersPositionsShowdown;
    }
}