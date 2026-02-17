<?php

declare(strict_types=1);

namespace App;

use App\Util\Random;

final class RailwayStation
{
    private int $tracksCount;

    /** @var array<int, string|null> trackNumber => trainId (or null if free) */
    private array $tracks = [];

    private int $waitingPassengers;

    public function __construct(int $tracksCount, int $waitingPassengers = 0)
    {
        if ($tracksCount <= 0) {
            throw new \InvalidArgumentException('tracksCount must be > 0');
        }
        if ($waitingPassengers < 0) {
            throw new \InvalidArgumentException('waitingPassengers must be >= 0');
        }

        $this->tracksCount = $tracksCount;
        $this->waitingPassengers = $waitingPassengers;

        for ($i = 1; $i <= $tracksCount; $i++) {
            $this->tracks[$i] = null;
        }
    }

    public function getWaitingPassengers(): int
    {
        return $this->waitingPassengers;
    }

    public function addWaitingPassengers(int $count): void
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('count must be >= 0');
        }

        $this->waitingPassengers += $count;
    }

    /**
     * Assegna il primo binario libero al treno (se esiste).
     * Ritorna il numero del binario, oppure null se nessun binario è libero.
     */
    public function assignFirstFreeTrack(string $trainId): ?int
    {
        foreach ($this->tracks as $trackNumber => $occupiedBy) {
            if ($occupiedBy === null) {
                $this->tracks[$trackNumber] = $trainId;
                return $trackNumber;
            }
        }

        return null;
    }

    public function releaseTrack(int $trackNumber, string $trainId): void
    {
        if (!array_key_exists($trackNumber, $this->tracks)) {
            throw new \InvalidArgumentException("Track $trackNumber does not exist");
        }

        if ($this->tracks[$trackNumber] !== $trainId) {
            $current = $this->tracks[$trackNumber];
            throw new \RuntimeException("Track $trackNumber is not occupied by train $trainId (current: " . ($current ?? 'free') . ")");
        }

        $this->tracks[$trackNumber] = null;
    }

    /**
     * Fa salire un numero random di passeggeri su un Train,
     * senza eccedere la capienza massima e senza scendere sotto zero in stazione.
     *
     * Ritorna quanti passeggeri sono saliti effettivamente.
     */
    public function boardRandomPassengers(Train $train, int $maxPerTurn = 80): int
    {
        if ($maxPerTurn < 0) {
            throw new \InvalidArgumentException('maxPerTurn must be >= 0');
        }

        if ($this->waitingPassengers === 0) {
            return 0;
        }

        $freeSeats = $train->getFreeSeats();
        if ($freeSeats === 0) {
            return 0;
        }

        $maxBoardable = min($this->waitingPassengers, $freeSeats, $maxPerTurn);
        if ($maxBoardable <= 0) {
            return 0;
        }

        $toBoard = Random::int(0, $maxBoardable);

        $train->addPassengers($toBoard);
        $this->waitingPassengers -= $toBoard;

        return $toBoard;
    }

    /**
     * Snapshot testuale dello stato dei binari.
     */
    public function tracksStatus(): string
    {
        $parts = [];
        foreach ($this->tracks as $trackNumber => $occupiedBy) {
            $parts[] = sprintf(
                'Binario %d: %s',
                $trackNumber,
                $occupiedBy === null ? 'LIBERO' : "OCCUPATO da {$occupiedBy}"
            );
        }

        return implode(PHP_EOL, $parts);
    }
}
