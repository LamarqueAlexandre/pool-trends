<?php

namespace App\Service;

class HandHistoryTransformer
{
    private array $allHands = [];
    private string $idHandHistory;

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
            }

            fclose($handle);

            dump($this->allHands);
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
}