<?php

namespace App\Service;

class HandHistoryTransformer
{
    private array $allHands = [];

    private string $idHandHistory;

    private array $playersSeats = [];

    private array $missingSeats = ['UTG', 'CO'];

    private array $playersWithoutPosition = [];

    private string $bettingRound = '';

    private string $showdown = '';

    private array $actions = ['calls', 'bets', 'raises', 'folds', 'checks'];

    public function convertHandHistoryToArray(string $fileToTransform)
    {
        $handle = fopen($fileToTransform, 'r');

        if ($handle) {

            while (($line = fgets($handle)) !== false) {
                // Récupération de l'identifiant de la HandHistory
                $this->getHandHistoryId($line);
                $this->addHandHistory();
                $this->addNoLimitHoldem($line);
                $this->addDateHandHistory($line);
                $this->getTheButton($line);
                $this->getPlayersSeats($line);
                $this->getSmallBlind($line);
                $this->getBigBlind($line);
                $this->getAllPlayersPositions();
                $this->getBettingRound($line);
                $this->getPlayersAction($line);
                $this->setShowdown($line);
                $this->getShowdown($line);
            }
            
            fclose($handle);

            return $this->allHands;
        } else {
            dump('Erreur lors de l\'ouverture du fichier');
            exit;
        }
    }

    private function getHandHistoryId(string $line)
    {
        if (preg_match('/#(\d+-\d+-\d+)/', $line, $matches)) {
            $this->idHandHistory = $matches[1];
            $this->bettingRound = '';
            $this->showdown = '';
        }
    }

    private function addHandHistory()
    {
        if (!isset($this->allHands[$this->idHandHistory])) {
            $this->allHands[$this->idHandHistory] = [];
        }
    }

    private function addNoLimitHoldem(string $line)
    {
        if (preg_match('/\((\d+\.\d+€\/\d+\.\d+€)\)/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["No-Limit Holdem"] = $matches[1];
        }
    }

    private function addDateHandHistory(string $line)
    {
        if (preg_match('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Date"] = $matches[1];
        }
    }

    private function getTheButton(string $line)
    {
        if (preg_match('/^(.*?) is the button/', $line, $matches)) {
            $buttonPosition = substr($line, strpos($line, "#") + 1, 1);
            $this->allHands[$this->idHandHistory]["Seats Position"]["Button"] = "Seat " . $buttonPosition;
        }
    }

    private function getSmallBlind(string $line)
    {
        if (preg_match('/^(.*?) posts small blind/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Seats Position"]["Small Blind"] = $this->getPlayerSeatByPseudo($matches[1]);
        }
    }

    private function getBigBlind(string $line)
    {
        if (preg_match('/^(.*?) posts big blind/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Seats Position"]["Big Blind"] = $this->getPlayerSeatByPseudo($matches[1]);
        }
    }

    private function getPlayersSeats(string $line)
    {
        if (preg_match('/^Seat \d+: .+ \(\d+(?:\.\d+)?€\)$/', $line)) {
            $playerPosition = explode(':', $line);
            $playerPosition[1] = ltrim(preg_replace('/ \(\d+(?:\.\d+)?€\)\n$/', '', $playerPosition[1]));
            $this->addPlayerSeat($playerPosition);
        }
    }

    private function addPlayerSeat(array $playerSeat)
    {
        $this->playersSeats[$this->idHandHistory][$playerSeat[0]] = $playerSeat[1];
        $this->allHands[$this->idHandHistory]["Seats"] = $this->playersSeats[$this->idHandHistory];
    }

    private function getPlayerSeatByPseudo(string $pseudo)
    {
        return array_search($pseudo, $this->playersSeats[$this->idHandHistory]);
    }

    private function getAllPlayersPositions()
    {
        if ($this->isArrayPlayersPositionCompleted() && !$this->isHeadsUpSituation()) {
            $this->playersWithoutPosition = array_values(array_diff(
                array_keys($this->allHands[$this->idHandHistory]['Seats']), 
                array_values($this->allHands[$this->idHandHistory]["Seats Position"]))
            );

            for ($i = 0; $i < count($this->playersWithoutPosition); $i++) {
                $this->playersWithoutPosition[$this->missingSeats[$i]] = $this->playersWithoutPosition[$i];
                unset($this->playersWithoutPosition[$i]);
            }
            
            if ($this->allHands[$this->idHandHistory]["Seats Position"]["Big Blind"] === "Seat 4") {
                $this->playersWithoutPosition = array_combine(
                    array_keys($this->playersWithoutPosition), array_reverse(array_values($this->playersWithoutPosition))
                );
            }

            if (count($this->allHands[$this->idHandHistory]["Seats"]) === 4) {
                $this->playersWithoutPosition["CO"] = $this->playersWithoutPosition["UTG"];
                unset($this->playersWithoutPosition["UTG"]);
            }

            $this->allHands[$this->idHandHistory]["Seats Position"] = array_merge(
                $this->allHands[$this->idHandHistory]["Seats Position"], $this->playersWithoutPosition
            );

            $playersPositions = [];

            foreach ($this->allHands[$this->idHandHistory]["Seats Position"] as $position => $seat) {
                if (isset($this->allHands[$this->idHandHistory]["Seats"][$seat])) {
                    $playersPositions[$position] = $this->allHands[$this->idHandHistory]["Seats"][$seat];
                }
            }

            $this->allHands[$this->idHandHistory]["Players Position"] = $playersPositions;
        }
    }

    private function isArrayPlayersPositionCompleted()
    {
        return (   isset($this->allHands[$this->idHandHistory]["Seats Position"])
                && count($this->allHands[$this->idHandHistory]["Seats Position"]) === 3);
    }

    private function isHeadsUpSituation()
    {
        return $this->allHands[$this->idHandHistory]["Seats Position"]["Button"] === $this->allHands[$this->idHandHistory]["Seats Position"]["Small Blind"];
    }

    private function getBettingRound(string $line)
    {
        if (preg_match('/\bPRE-FLOP\b/', $line)) {
            $this->bettingRound = "Preflop";
            return;
        }
        
        if (preg_match('/\bFLOP\b/', $line)) {
            $this->bettingRound = "Flop";
            return;
        }

        if (preg_match('/\bTURN\b/', $line)) {
            $this->bettingRound = "Turn";
            return;
        }
        
        if (preg_match('/\bRIVER\b/', $line)) {
            $this->bettingRound = "River";
            return;
        }
    }

    private function setShowdown(string $line)
    {
        if (preg_match('/\bSHOW DOWN\b/', $line)) {
            $this->showdown = "Show Down";
            return;
        }
    }

    private function getShowdown(string $line)
    {
        if (!empty($this->showdown)) {
            if (preg_match('/^(.*?)\s+shows\s+\[(.*?)\]\s+\(.*\)$/', $line, $matches)) {
                $this->allHands[$this->idHandHistory][$this->showdown][$matches[1]] = $matches[2];
            }
        }
    }

    private function getPlayersAction(string $line)
    {
        if (!empty($this->bettingRound)) {
            foreach ($this->actions as $action) {
                $pattern = '/\b' . $action . '\b/';
                if (preg_match($pattern, $line)) {
                    $motif = '/\b(' . $action . ')\b/';
                    $playerAction = preg_split($motif, $line, 2, PREG_SPLIT_DELIM_CAPTURE);
                    
                    /**
                     * Si on trouve une action, on ajoute le joueur et son action 
                     * dans le "round" correspondant
                     */
                    $this->allHands[$this->idHandHistory][$this->bettingRound][] = [trim($playerAction[0]) => $playerAction[1]];
                }
            }
        }
    }
}