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

                /**
                 * 
                 */

            } catch (\Exception $e) {
                dump($id);
            }

            dump($this->spotsConfigurations);
            exit;
        }
    }

    private function getDataFromSpot(array $showdown, array $playersPositions)
    {
        $matchingRows = [];
        $positions = [];

        foreach ($playersPositions as $position => $pseudo) {
            if (array_key_exists($pseudo, $showdown)) {
                $matchingRows[$position] = $pseudo;
            }
        }

        $positions = array_reverse(array_keys($matchingRows));

        /**
         * Trouver un moyen de définir qui chaine de caractère qui précise le spot
         * 
         * Exemple : "Button vs SB vs BB"
         *           "Button vs BB"
         */
        $configuration = implode(" vs ", $positions);

        if (!isset($this->spotsConfigurations[$configuration])) {
            $this->spotsConfigurations[$configuration] = [];
        }

        $result = [];

        foreach ($positions as $position => $pseudo) {

            dump($position);
            dump($pseudo);
            dump($showdown);

            dump(array_key_exists('J1mmy Conway', $showdown));

            exit;

            if (array_key_exists($pseudo, $showdown)) {
                $result[$position] = $showdown[$pseudo];
            }
        }

        dump($result);
        exit;

    }
}