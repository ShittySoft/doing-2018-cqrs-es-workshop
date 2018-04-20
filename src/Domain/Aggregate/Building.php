<?php

declare(strict_types=1);

namespace Building\Domain\Aggregate;

use Building\Domain\DomainEvent\CheckInAnomalyDetected;
use Building\Domain\DomainEvent\NewBuildingWasRegistered;
use Building\Domain\DomainEvent\UserCheckedIn;
use Building\Domain\DomainEvent\UserCheckedOut;
use Prooph\EventSourcing\AggregateRoot;
use Rhumsaa\Uuid\Uuid;

final class Building extends AggregateRoot
{
    /**
     * @var Uuid
     */
    private $uuid;

    /**
     * @var string
     */
    private $name;

    /** @var <string, null>[] indexed by username */
    private $checkedIn = [];

    public static function new(string $name) : self
    {
        $self = new self();

        $self->recordThat(NewBuildingWasRegistered::occur(
            (string) Uuid::uuid4(),
            [
                'name' => $name,
            ]
        ));

        return $self;
    }

    public function checkInUser(string $username)
    {
        $anomaly = array_key_exists($username, $this->checkedIn);

        $this->recordThat(UserCheckedIn::toBuilding(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::inBuildingForUser(
                $this->uuid,
                $username
            ));
        }
    }

    public function checkOutUser(string $username)
    {
        $anomaly = ! array_key_exists($username, $this->checkedIn);

        $this->recordThat(UserCheckedOut::ofBuilding(
            $this->uuid,
            $username
        ));

        if ($anomaly) {
            $this->recordThat(CheckInAnomalyDetected::inBuildingForUser(
                $this->uuid,
                $username
            ));
        }
    }

    public function whenNewBuildingWasRegistered(NewBuildingWasRegistered $event)
    {
        $this->uuid = Uuid::fromString($event->aggregateId());
        $this->name = $event->name();
    }

    public function whenUserCheckedIn(UserCheckedIn $event)
    {
        $this->checkedIn[$event->username()] = null;
    }

    public function whenUserCheckedOut(UserCheckedOut $event)
    {
        unset($this->checkedIn[$event->username()]);
    }

    public function whenCheckInAnomalyDetected(CheckInAnomalyDetected $event)
    {
        // nothing to do here
    }

    /**
     * {@inheritDoc}
     */
    protected function aggregateId() : string
    {
        return (string) $this->uuid;
    }

    /**
     * {@inheritDoc}
     */
    public function id() : string
    {
        return $this->aggregateId();
    }
}
