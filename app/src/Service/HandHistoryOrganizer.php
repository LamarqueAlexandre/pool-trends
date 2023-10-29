<?php

namespace App\Service;

class HandHistoryOrganizer
{
    private array $limpedHands = [];

    private array $singleRaisedHands = [];

    private array $threeBettedHAnds = [];

    private array $fourBettedHands = [];

    private array $fiveBettedHands = [];

    private array $sixBettedHands = [];

    private array $undefinedActionHands = [];

    private array $organizedHands = [];

    public function organizeHAnds(array $hands)
    {
        // Récupérer toutes les actions
        $actions = [];

        // Récupérer les showdowns => pseudo et hand
        foreach ($hands as $hand) {
            foreach ($hand["Preflop"] as $playerActions) {
                foreach ($playerActions as $action) {
                    $actions[] = $action;
                }
            }

            $totalActions = array_count_values($actions);

            if (!isset($totalActions['raises'])) {
                if (!isset($totalActions['calls'])) {
                    $this->undefinedActionHands[] = $hand;
                }

                if (isset($totalActions['calls'])) {
                    $this->limpedHands[] = $hand;
                }
            }

            if (isset($totalActions['raises'])) {
                if ($totalActions['raises'] === 1) {
                    $this->singleRaisedHands[] = $hand;
                }

                if ($totalActions['raises'] === 2) {
                    $this->threeBettedHAnds[] = $hand;
                }

                if ($totalActions['raises'] === 3) {
                    $this->fourBettedHands[] = $hand;
                }

                if ($totalActions['raises'] === 4) {
                    $this->fiveBettedHands[] = $hand;
                }

                if ($totalActions['raises'] === 5) {
                    $this->sixBettedHands[] = $hand;
                }
            }

            $actions = [];
            $totalActions = [];
        }

        $this->mergeOrganizedHands();

        return $this->organizedHands;
    }

    private function mergeOrganizedHands()
    {
        $this->organizedHands['LimpedHands'] = $this->limpedHands;
        $this->organizedHands['UndefinedAction'] = $this->undefinedActionHands;
        $this->organizedHands['SingleRaiseHands'] = $this->singleRaisedHands;
        $this->organizedHands['ThreeBettedHands'] = $this->threeBettedHAnds;
        $this->organizedHands['FourBettedHands'] = $this->fourBettedHands;
        $this->organizedHands['FiveBettedHands'] = $this->fiveBettedHands;
        $this->organizedHands['SixBettedHands'] = $this->sixBettedHands;
    } 
}