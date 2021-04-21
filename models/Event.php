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
                    foreach ($fights as $fight) {
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

    /**
     * Return list of events in descending order by event date.
     *
     * @param int $limit the number of events to return
     * @param int $start the event to start from
     * @param bool $upcoming
     *  true - returns upcoming events only
     *  false - returns only past events
     *  null - does not filter
     * @return array
     */
    public function getAll(int $limit, int $start, bool $upcoming = null): array
    {
        $filter = "";
        if (!is_null($upcoming)) {
            $date = date('Y-m-d');
            if ($upcoming) {
                $filter = "WHERE EventDate >= $date";
            } else {
                $filter = "WHERE EventDate < $date";
            }

        }

        $query = "SELECT * FROM Events $filter ORDER BY EventDate DESC LIMIT $start, $limit";
        try {
            $query = $this->db->query($query);

            $result = $query->fetchAll();
            $this->results = $result;

            return $result;
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    /**
     * Retrieves the total records in the database - used for pagination, to calculate pages.
     *
     * @return int total number of records.
     */
    public function getTotal(): int
    {
        $query = $this->db->query("SELECT * FROM Events");
        return $query->rowCount();
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