<?php

namespace App\Service;

class HandHistoryTransformer
{
    private array $allHands = [];
    private string $idHandHistory;

    private array $playersSeats = [];

    private array $missingSeats = ['UTG', 'CO'];

    private array $playersWithoutPosition = [];

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
            }

            fclose($handle);

            foreach ($this->allHands as $id => $hand) {
                dump($id);
                dump($hand);
            }
            // dump($this->allHands);
            exit;

            return $this->allHands;
        } else {
            dump('Erreur lors de l\'ouverture du fichier');
            exit;
        }
    }

    public function getHandHistoryId(string $line)
    {
        if (preg_match('/#(\d+-\d+-\d+)/', $line, $matches)) {
            $this->idHandHistory = $matches[1];
        }
    }

    public function addHandHistory()
    {
        if (!isset($this->allHands[$this->idHandHistory])) {
            $this->allHands[$this->idHandHistory] = [];
        }
    }

    public function addNoLimitHoldem(string $line)
    {
        if (preg_match('/\((\d+\.\d+€\/\d+\.\d+€)\)/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["No-Limit Holdem"] = $matches[1];
        }
    }

    public function addDateHandHistory(string $line)
    {
        if (preg_match('/(\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Date"] = $matches[1];
        }
    }

    public function getTheButton(string $line)
    {
        if (preg_match('/^(.*?) is the button/', $line, $matches)) {
            $buttonPosition = substr($line, strpos($line, "#") + 1, 1);
            $this->allHands[$this->idHandHistory]["Players Position"]["Button"] = "Seat " . $buttonPosition;
        }
    }

    public function getSmallBlind(string $line)
    {
        if (preg_match('/^(.*?) posts small blind/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Players Position"]["Small Blind"] = $this->getPlayerSeatByPseudo($matches[1]);
        }
    }

    public function getBigBlind(string $line)
    {
        if (preg_match('/^(.*?) posts big blind/', $line, $matches)) {
            $this->allHands[$this->idHandHistory]["Players Position"]["Big Blind"] = $this->getPlayerSeatByPseudo($matches[1]);
        }
    }

    public function getPlayersSeats(string $line)
    {
        if (preg_match('/^Seat \d+: .+ \(\d+(?:\.\d+)?€\)$/', $line)) {
            $playerPosition = explode(':', $line);
            $playerPosition[1] = ltrim(preg_replace('/ \(\d+(?:\.\d+)?€\)\n$/', '', $playerPosition[1]));
            $this->addPlayerSeat($playerPosition);
        }
    }

    public function addPlayerSeat(array $playerSeat)
    {
        $this->playersSeats[$this->idHandHistory][$playerSeat[0]] = $playerSeat[1];
        $this->allHands[$this->idHandHistory]["Seats"] = $this->playersSeats[$this->idHandHistory];
    }

    public function getPlayerSeatByPseudo(string $pseudo)
    {
        return array_search($pseudo, $this->playersSeats[$this->idHandHistory]);
    }

    public function getAllPlayersPositions()
    {
        if ($this->isArrayPlayersPositionIsCompleted() && !$this->isHeadsUpSituation()) {
            $this->playersWithoutPosition = array_values(array_diff(
                array_keys($this->allHands[$this->idHandHistory]['Seats']), 
                array_values($this->allHands[$this->idHandHistory]["Players Position"]))
            );

            for ($i = 0; $i < count($this->playersWithoutPosition); $i++) {
                $this->playersWithoutPosition[$this->missingSeats[$i]] = $this->playersWithoutPosition[$i];
                unset($this->playersWithoutPosition[$i]);
            }
            
            if ($this->allHands[$this->idHandHistory]["Players Position"]["Big Blind"] === "Seat 4") {
                $this->playersWithoutPosition = array_combine(
                    array_keys($this->playersWithoutPosition), array_reverse(array_values($this->playersWithoutPosition))
                );
            }

            $this->allHands[$this->idHandHistory]["Players Position"] = array_merge(
                $this->allHands[$this->idHandHistory]["Players Position"], $this->playersWithoutPosition
            );
        }
    }

    public function isArrayPlayersPositionIsCompleted()
    {
        return (   isset($this->allHands[$this->idHandHistory]["Players Position"])
                && count($this->allHands[$this->idHandHistory]["Players Position"]) === 3);
    }

    public function isHeadsUpSituation()
    {
        return $this->allHands[$this->idHandHistory]["Players Position"]["Button"] === $this->allHands[$this->idHandHistory]["Players Position"]["Small Blind"];
    }
}