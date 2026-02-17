<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\RailwayStation;
use App\Train;

final class TrainTest extends TestCase
{
    public function test_constructor_validates_id_capacity_and_passengers(): void
    {
        //$this->markTestIncomplete('Testa validazioni: id non vuoto, maxCapacity >= 1, passengers >= 0 e <= maxCapacity.');
        $train = new Train('T1', 100, 50);
        self::assertSame('T1', $train->getId());
        //$this->assertSame('T1', $train->getId());    
        self::assertSame(100, $train->getCapacity());
        self::assertSame(50, $train->getPassengers());

        $this->expectException(\InvalidArgumentException::class);
        new Train('', 100, 50);

        $this->expectException(\InvalidArgumentException::class);
        new Train('T1', 0, 0);
    }

    public function test_request_track_sets_current_track_when_assigned(): void
    {
        //$this->markTestIncomplete('Testa che requestTrack() ritorni il binario assegnato e imposti currentTrack() coerentemente.');
        $station = new RailwayStation(2);
        $train = new Train('T1', 100, 0);
        $assignedTrack = $train->requestTrack($station);
        // Deve assegnare il primo binario
        $this->assertSame(1, $assignedTrack);
        // Il treno deve sapere su quale binario si trova
        $this->assertSame(1, $train->getAssignedTrack());
        // Deve risultare in stazione
        $this->assertTrue($train->isInStation());

    }

    public function test_request_track_sets_current_track_to_null_when_station_is_full(): void
    {
        //$this->markTestIncomplete('Testa che se la stazione non ha binari liberi, requestTrack() ritorni null e currentTrack() sia null.');
    }

    public function test_disembark_decreases_passengers_and_adds_random_waiting_passengers(): void
    {
        //$this->markTestIncomplete('Testa disembark(): i passeggeri a bordo diminuiscono; una parte (controllata con FixedRandomizer) viene aggiunta ai waitingPassengers della stazione; il metodo ritorna quel numero.');
    }

    public function test_disembark_never_disembarks_more_than_on_board(): void
    {
        //$this->markTestIncomplete('Testa che se chiedi di far scendere più passeggeri di quelli presenti, scendano solo quelli disponibili (passengers non va sotto zero).');
    }

    public function test_board_increases_passengers_and_throws_if_exceeds_capacity(): void
    {
        //$this->markTestIncomplete('Testa board(): aumenta passengers; se supera maxCapacity lancia DomainException.');
    }

    public function test_depart_releases_track_and_resets_current_track(): void
    {
        //$this->markTestIncomplete('Testa che depart() liberi il binario in stazione e imposti currentTrack() a null.');
    }
}
