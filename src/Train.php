<?php

declare(strict_types=1);

namespace App;

use App\Util\Random;

final class Train
{
    private string $id;
    private int $passengers;
    private int $capacity;

    private ?RailwayStation $station = null;
    private ?int $assignedTrack = null;

    public function __construct(string $id, int $capacity, int $passengers = 0)
    {
        if ($capacity <= 0) {
            throw new \InvalidArgumentException('capacity must be > 0');
        }
        if ($passengers < 0) {
            throw new \InvalidArgumentException('passengers must be >= 0');
        }
        if ($passengers > $capacity) {
            throw new \InvalidArgumentException('passengers cannot exceed capacity');
        }

        $this->id = $id;
        $this->capacity = $capacity;
        $this->passengers = $passengers;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPassengers(): int
    {
        return $this->passengers;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getFreeSeats(): int
    {
        return $this->capacity - $this->passengers;
    }

    public function isInStation(): bool
    {
        return $this->station !== null && $this->assignedTrack !== null;
    }

    public function getAssignedTrack(): ?int
    {
        return $this->assignedTrack;
    }

    /**
     * Chiede alla stazione un binario libero.
     * Se disponibile, il primo binario libero viene assegnato al treno.
     */
    public function requestTrack(RailwayStation $station): ?int
    {
        if ($this->isInStation()) {
            throw new \RuntimeException("Train {$this->id} is already in a station (track {$this->assignedTrack})");
        }

        $track = $station->assignFirstFreeTrack($this->id);
        if ($track === null) {
            return null;
        }

        $this->station = $station;
        $this->assignedTrack = $track;

        return $track;
    }

    /**
     * Fa scendere un certo numero di passeggeri.
     * Quelli scesi vengono aggiunti *in parte (random)* ai passeggeri in attesa in stazione.
     *
     * Ritorna: [droppedOffTotal, addedToWaiting]
     */
    public function dropOffPassengers(int $count): array
    {
        if (!$this->isInStation()) {
            throw new \RuntimeException("Train {$this->id} is not in a station");
        }
        if ($count < 0) {
            throw new \InvalidArgumentException('count must be >= 0');
        }

        $drop = min($count, $this->passengers);
        $this->passengers -= $drop;

        // Solo una parte (random) resta in attesa in stazione: il resto "esce" dalla stazione.
        $toWaiting = ($drop === 0) ? 0 : Random::int(0, $drop);

        $this->station->addWaitingPassengers($toWaiting);

        return [$drop, $toWaiting];
    }

    /**
     * Aggiunge passeggeri (usato quando salgono dalla stazione).
     */
    public function addPassengers(int $count): void
    {
        if ($count < 0) {
            throw new \InvalidArgumentException('count must be >= 0');
        }
        if ($this->passengers + $count > $this->capacity) {
            throw new \RuntimeException("Cannot add $count passengers: would exceed capacity");
        }

        $this->passengers += $count;
    }

    /**
     * Abbandona la stazione liberando il binario.
     */
    public function leaveStation(): void
    {
        if (!$this->isInStation()) {
            throw new \RuntimeException("Train {$this->id} is not in a station");
        }

        $this->station->releaseTrack($this->assignedTrack, $this->id);

        $this->station = null;
        $this->assignedTrack = null;
    }
}
//testare modo in cui restituisce passeggeri non random