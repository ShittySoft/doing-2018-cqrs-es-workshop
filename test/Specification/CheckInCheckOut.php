<?php

declare(strict_types=1);

namespace Specification;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Building\Domain\Aggregate\Building;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\EventStoreIntegration\AggregateTranslator;
use Prooph\EventStore\Aggregate\AggregateType;
use Rhumsaa\Uuid\Uuid;

// I "context" di Behat sono semplicemente dei test runner. Il modo piu' semplice
// di approcciare la cosa se conoscete PHPUnit e' che i file `.feature` sono
// "data-provider", e il context e' pieno di metodi "test" che usano quei provider
final class CheckInCheckOut implements Context
{
    /** @var AggregateChanged[] */
    private $pastEvents = [];

    /** @var Building[] */
    private $aggregate;


// Snake case fa miracoli per la leggibilita' nei test!
//
// Behat fa un semplice regex match di queste annotazioni contro i file `.feature`,
// e poi chiama questi metodi (dopo aver istanziato il context) uno ad uno.
// Se non ci sono exception => success. Quindi le exception vanno lanciate dal
// programmatore. Suggerisco usare `beberlei/assert` o `webmozart/assert` per il
// testing qui dentro.
//
// I parametri sono estratti dalla regex e messi nella stessa posizione in cui sono
// trovati nella chiamata al metodo.
//
// A quanto pare le regex non stavano funzionando...

    /**
     * @Given /^the building "([^"]+)" was registered$/
     */
    public function the_building_was_registered(string $name) : void
    {
        // Qui dobbiamo registrare un "evento passato" (Given)
        // Usiamo la stessa API che useremmo dentro l'aggregate
        $this->pastEvents[] = NewBuildingWasRegistered::occur(
            Uuid::uuid4()->toString(),
            ['name' => $name]
        );
    }

    /**
     * @When /^"([^"]+)" checks into the "([^"]+)" building$/
     */
    public function user_checks_into_building(string $username, string $buildingName) : void
    {
        $this->aggregate($buildingName)->checkInUser($username);
    }

    /**
     * @Then /^"([^"]+)" should have been checked into the "([^"]+)" building$/
     */
    public function user_should_have_been_checked_into_building(string $username, string $buildingName) : void
    {
        /** @var $recordedEvent UserCheckedIn */
        $recordedEvent = $this->popRecordedEvent($buildingName);

        Assertion::isInstanceOf($recordedEvent, UserCheckedIn::class);
        Assertion::same($username, $recordedEvent->username());
    }

    private function aggregate(string $buildingName) : Building
    {
        if (isset($this->aggregate[$buildingName])) {
            return $this->aggregate[$buildingName];
        }

        // I tipi non corrispondono, ma yolo
        return $this->aggregate[$buildingName] = (new AggregateTranslator()) // prooph to the rescue
            ->reconstituteAggregateFromHistory(
                AggregateType::fromAggregateRootClass(Building::class),
                new \ArrayIterator($this->pastEvents)
            );
    }

    private function popRecordedEvent(string $buildingName) : AggregateChanged
    {
        if (isset($this->pastEvents[$buildingName])) {
            // qual'era "piglia il primo elemento dell'array?"
            // Notare: ci possono essere piu' eventi registrati - dobbiamo verificare ognuno
            // degli eventi con un `Then` separato!
            return array_shift($this->pastEvents[$buildingName]); // rimuovo il primo elemento
        }

        $this->pastEvents[$buildingName] = (new AggregateTranslator())
            ->extractPendingStreamEvents($this->aggregate($buildingName));

        return array_shift($this->pastEvents[$buildingName]);
    }
}
