<?php
//istanza, classe; self (metodo statico appartiene alla classe; muore subito) this (appartiene alle istanze)
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\RailwayStation;
use App\Train;

final class RailwayStationTest extends TestCase
{
    public function test_constructor_validates_tracks_and_waiting_passengers(): void
    {
        //$this->markTestIncomplete('Testa che tracks >= 1 e waitingPassengers >= 0; devono lanciare InvalidArgumentException in caso contrario.')
        $station = new RailwayStation(3, 100); //tracks, waitingPassangers
        $this->assertSame(100, $station->getWaitingPassengers());
        $this->expectException(\InvalidArgumentException::class);
        new RailwayStation(0, 100);
    }

    public function test_assign_first_free_track_returns_first_free_track_and_null_when_full(): void
    {
        //$this->markTestIncomplete('Testa che i binari vengano assegnati in ordine (1..N) e che quando sono tutti occupati ritorni null.');
        $station = new RailwayStation(2);
        $track1 = $station->assignFirstFreeTrack('T1');
        $track2 = $station->assignFirstFreeTrack('T2');
        $this->assertSame(1, $track1);
        $this->assertSame(2, $track2);
        $this->assertNull($station->assignFirstFreeTrack('T3'));
    }

    public function test_assign_first_free_track_is_idempotent_for_same_train(): void
    {
        //$this->markTestIncomplete('Testa che chiamando assignFirstFreeTrack due volte per lo stesso trainId venga ritornato lo stesso binario già assegnato.');
        $station = new RailwayStation(3);
        $first = $station->assignFirstFreeTrack('T1');
        $second = $station->assignFirstFreeTrack('T1');
        $this->assertSame($first, $second);
        $this->assertSame(1, $first);
    }

    public function test_release_track_frees_the_track_for_new_trains(): void
    {
        //$this->markTestIncomplete('Testa che releaseTrack() liberi il binario e permetta ad un nuovo treno di ottenerlo.');
        $station = new RailwayStation(1);
        // T1 prende il binario 1
        $track1 = $station->assignFirstFreeTrack('T1');
        $this->assertSame(1, $track1);
        // Rilascia il binario
        $station->releaseTrack(1, 'T1');
        // T2 deve poter ottenere lo stesso binario
        $track2 = $station->assignFirstFreeTrack('T2');
        $this->assertSame(1, $track2);
    
    }

    public function test_board_passengers_boards_between_0_and_capacity_and_decreases_waiting(): void
    {
        //$this->markTestIncomplete('Testa boardPassengers(): usa FixedRandomizer per forzare quanti salgono; verifica decremento waitingPassengers e incremento passeggeri sul treno senza superare capienza.');
        $station = new RailwayStation(1, 100);
        $train = new Train('T1', 50, 0);
        $initialWaiting = $station->getWaitingPassengers();
        $boarded = $station->boardPassengers($train, 30);
        // Deve essere tra 0 e 30
        $this->assertGreaterThanOrEqual(0, $boarded);
        $this->assertLessThanOrEqual(30, $boarded);
        // Non deve superare capienza
        $this->assertLessThanOrEqual(50, $train->getPassengers());
        // waiting deve diminuire correttamente
        $this->assertSame(
            $initialWaiting - $boarded,
            $station->getWaitingPassengers());
        }

    public function test_board_passengers_returns_zero_when_no_waiting_or_no_capacity(): void
    {
        //$this->markTestIncomplete('Testa che boardPassengers() ritorni 0 quando waitingPassengers=0 o quando il treno è già pieno.');
        // Caso 1: nessuno in attesa
        $station = new RailwayStation(1, 0);
        $train = new Train('T1', 50, 0);
        $boarded = $station->boardPassengers($train, 30);
        $this->assertSame(0, $boarded);
        // Caso 2: treno pieno
        $station2 = new RailwayStation(1, 100);
        $trainFull = new Train('T2', 50, 50);
        $boarded2 = $station2->boardPassengers($trainFull, 30);
        $this->assertSame(0, $boarded2);
    }
        
}
