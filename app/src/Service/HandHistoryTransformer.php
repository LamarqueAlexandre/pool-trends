<?php

namespace App\Service;

class HandHistoryTransformer
{
    private array $allHands = [];
    private string $idHandHistory;

    private array $playersSeats = [];

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
            }

            fclose($handle);

            dump($this->playersSeats);
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
        if (strpos($line, "is the button") !== false) {
            $buttonPosition = substr($line, strpos($line, "#") + 1, 1);
            $this->addButton($buttonPosition);
        }
    }

    public function addButton(string $numberSeat)
    {
        $this->allHands[$this->idHandHistory]["Players Position"]["Button"] = "Seat " . $numberSeat;
    }

    public function getSmallBlind(string $line)
    {
        if (strpos($line, "small blind") !== false) {
            $explodedLine = explode(' ', $line);
            $seat = $this->getPlayerSeatByPseudo($explodedLine[0]);
            $this->allHands[$this->idHandHistory]["Players Position"]["Small Blind"] = $seat;
        }
    }

    public function getBigBlind(string $line)
    {
        if (strpos($line, "big blind") !== false) {
            $explodedLine = explode(' ', $line);
            $seat = $this->getPlayerSeatByPseudo($explodedLine[0]);
            $this->allHands[$this->idHandHistory]["Players Position"]["Big Blind"] = $seat;
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

        // if (!isset($this->allHands[$this->idHandHistory]["Seats"])) {
        //     $this->allHands[$this->idHandHistory]["Seats"][] = $this->playersSeats;
        // }
    }

    public function getPlayerSeatByPseudo(string $pseudo)
    {
        return array_search($pseudo, $this->playersSeats);
    }
}