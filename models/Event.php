<?php

namespace models;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOException;
use TypeError;

class Event
{
    const DATE_MIN = '1993-11-12'; // date of first ever event
    const PERMISSION_AREA = 'EVENTS';

    private ?int $eventId = null;
    private ?string $location = null;
    private ?string $date = null;
    private $results = null;

    private PDO $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getOne(int $id)
    {
        // performs validation checks before setting
        $this->setEventId($id);

        $query = "SELECT * FROM Events WHERE EventID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->eventId]);

            if ($query->rowCount() > 0) {
                $event = $query->fetch();

                $this->location = $event['EventLocation'];
                $this->date = $event['EventDate'];

                $this->results = $event;

                // get fights from event
                $query = "SELECT * FROM Fights WHERE EventID = ?";
                $query = $this->db->prepare($query);
                $query->execute([$this->eventId]);

                $event_data = $event;
                $event_data['Fights'] = [];

                if ($query->rowCount() > 0) {
                    $fights = $query->fetchAll();
                    foreach($fights as $fight) {
                        $athlete = $this->db->prepare("SELECT AthleteID FROM FightAthletes WHERE FightID=?;");
                        $athlete->execute([$fight['FightID']]);

                        if ($athlete->rowCount() > 0) {
                            $athletes = $athlete->fetchAll();
                            $athlete_data['Athletes'] = $athletes;
                            array_push($event_data['Fights'], array_merge($fight, $athlete_data));
                        }
                    }
                }
                return $event_data;
            }

            return false;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function getAll()
    {
        $query = "SELECT * FROM Events";
        try {
            $query = $this->db->query($query);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }


    public function create(array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        $query = "INSERT INTO Events (EventLocation, EventDate) VALUES (?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->location, $this->date]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function update(int $id, array $data = null): int
    {
        $this->setEventId($id);

        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        $query = "UPDATE Events 
                    SET 
                        EventLocation = ?, 
                        EventDate = ?
                    WHERE 
                        EventID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->location, $this->date, $this->eventId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function delete(int $id): int
    {
        $this->setEventId($id);

        $query = "DELETE FROM Events WHERE EventID = ?";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->eventId]);

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    // utility functions
    private function processData(array $data): void
    {
        try {
            $this->setDate($data['EventDate']);
            $this->setLocation($data['EventLocation']);
        } catch (Exception | TypeError $exception) {
            exit($exception->getMessage());
        }
    }

    private function validateData(): void
    {
        if (is_null($this->location) || is_null($this->date)) {
            throw new InvalidArgumentException("All object variables must have a value");
        }
    }

    // getters and setters
    /**
     * @return int
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @param int $eventId
     */
    public function setEventId(int $eventId): void
    {
        if ($eventId <= 0) {
            throw new InvalidArgumentException("Invalid Event ID");
        }
        $this->eventId = $eventId;
    }

    /**
     * @return string
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     */
    public function setLocation(string $location): void
    {
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getDate(): ?string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        if (!strtotime($date)) {
            throw new InvalidArgumentException("Invalid event date");
        }

        if (strtotime(self::DATE_MIN) > strtotime($date)) {
            throw new InvalidArgumentException("Date must be on or after " . self::DATE_MIN);
        }

        $this->date = date("Y-m-d", strtotime($date));
    }

    /**
     * @return null
     */
    public function getResults()
    {
        return $this->results;
    }
}