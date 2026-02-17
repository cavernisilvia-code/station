#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\RailwayStation;
use App\Train;
use App\Util\Random;

function prompt(string $msg): string {
    echo $msg;
    $line = fgets(STDIN);
    return $line === false ? '' : trim($line);
}

function println(string $msg = ''): void {
    echo $msg . PHP_EOL;
}

$station = new RailwayStation(tracksCount: 4, waitingPassengers: 220);

// Registry semplice dei treni “conosciuti” dalla simulazione.
$trains = [
    'T1' => new Train('T1', capacity: 180, passengers: 140),
    'T2' => new Train('T2', capacity: 120, passengers: 60),
    'T3' => new Train('T3', capacity: 220, passengers: 200),
    'T4' => new Train('T3', capacity: 220, passengers: 200),
    'T5' => new Train('T3', capacity: 120, passengers: 80),
    'T6' => new Train('T3', capacity: 120, passengers: 50),
];

println("=== Train Station Simulator (CLI) ===");
println("Comandi: help, status, arrive <ID>, drop <ID> <N>, board <ID>, leave <ID>, tick, quit");
println();

while (true) {
    $raw = prompt("> ");
    if ($raw === '') {
        continue;
    }

    $parts = preg_split('/\s+/', $raw) ?: [];
    $cmd = strtolower($parts[0] ?? '');

    try {
        switch ($cmd) {
            case 'help':
                println("help                          Mostra comandi");
                println("status                        Stato binari, stazione e treni");
                println("arrive <ID>                   Il treno chiede un binario libero");
                println("drop <ID> <N>                 Il treno fa scendere N passeggeri (parte random resta in attesa)");
                println("board <ID>                    La stazione fa salire un numero random di passeggeri sul treno");
                println("leave <ID>                    Il treno lascia la stazione (libera il binario)");
                println("tick <N>                      Esegue N piccole 'turnazioni' random su treni a caso");
                println("quit                          Esci");
                break;

            case 'status':
                println("--- Station ---");
                println("Passeggeri in attesa: " . $station->getWaitingPassengers());
                println($station->tracksStatus());
                println();
                println("--- Trains ---");
                foreach ($trains as $id => $t) {
                    $where = $t->isInStation()
                        ? "IN STAZIONE (binario " . $t->getAssignedTrack() . ")"
                        : "FUORI STAZIONE";
                    println(sprintf(
                        "%s | pax %d/%d | %s",
                        $id,
                        $t->getPassengers(),
                        $t->getCapacity(),
                        $where
                    ));
                }
                break;

            case 'arrive': {
                $id = $parts[1] ?? '';
                if (!isset($trains[$id])) {
                    throw new RuntimeException("Unknown train: $id");
                }
                $track = $trains[$id]->requestTrack($station);
                if ($track === null) {
                    println("Nessun binario libero: il treno $id resta in attesa fuori stazione.");
                } else {
                    println("Treno $id assegnato al binario $track.");
                }
                break;
            }

            case 'drop': {
                $id = $parts[1] ?? '';
                $n  = isset($parts[2]) ? (int)$parts[2] : -1;

                if (!isset($trains[$id])) {
                    throw new RuntimeException("Unknown train: $id");
                }
                if ($n < 0) {
                    throw new RuntimeException("Usage: drop <ID> <N>");
                }

                [$dropped, $toWaiting] = $trains[$id]->dropOffPassengers($n);
                println("Treno $id: scesi $dropped passeggeri. In attesa in stazione aggiunti $toWaiting.");
                break;
            }

            case 'board': {
                $id = $parts[1] ?? '';
                if (!isset($trains[$id])) {
                    throw new RuntimeException("Unknown train: $id");
                }
                if (!$trains[$id]->isInStation()) {
                    throw new RuntimeException("Train $id is not in station");
                }

                $boarded = $station->boardRandomPassengers($trains[$id], maxPerTurn: 80);
                println("Treno $id: saliti $boarded passeggeri.");
                break;
            }

            case 'leave': {
                $id = $parts[1] ?? '';
                if (!isset($trains[$id])) {
                    throw new RuntimeException("Unknown train: $id");
                }
                $trains[$id]->leaveStation();
                println("Treno $id ha lasciato la stazione.");
                break;
            }

            case 'tick': {
                $steps = (int)($parts[1] ?? 1);
                $ids = array_keys($trains);
                $action = 1;
                while ($steps > 0) {
                    $id = $ids[Random::int(0, count($ids) - 1)];
                    $t = $trains[$id];

                    println("Azione [$action]:");
                    if (!$t->isInStation()) {
                        $track = $t->requestTrack($station);
                        if ($track === null) {
                            println("- [tick] $id prova ad arrivare, ma non ci sono binari liberi.");
                        } else {
                            println("- [tick] $id arriva ed entra al binario $track.");
                        }
                    } else {
                        $toDrop = Random::int(0, max(0, $t->getPassengers()));
                        [$dropped, $toWaiting] = $t->dropOffPassengers($toDrop);
                        println("- [tick] $id scarica $dropped (in attesa: +$toWaiting).");
                        $boarded = $station->boardRandomPassengers($t, maxPerTurn: 80);
                        println("- [tick] $id carica $boarded passeggeri.");
                        $t->leaveStation();
                        println("- [tick] $id parte e libera il binario.");
                    }
                    $steps--;
                    $action++;
                    println("");
                }
                break;
            }

            case 'q':
            case 'quit':
            case 'exit':
                println("Bye.");
                exit(0);

            default:
                println("Comando sconosciuto. Digita 'help'.");
        }
    } catch (Throwable $e) {
        println("Errore: " . $e->getMessage());
    }
}
