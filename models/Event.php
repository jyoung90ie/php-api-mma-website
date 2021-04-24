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
                $query = "SELECT F.*,
                                WC.WeightClass,
                                R.RefereeName,
                                FR.WinnerAthleteID
                            FROM Fights F 
                            LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                            LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                            LEFT JOIN FightResults FR on F.FightID = FR.FightID
                            WHERE F.EventID = ?";
                $query = $this->db->prepare($query);
                $query->execute([$this->eventId]);

                $eventData = $event;
                $eventData['Fights'] = [];
                $athleteData['Athletes'] = [];

                if ($query->rowCount() > 0) {
                    $fights = $query->fetchAll();
                    foreach ($fights as $fight) {
                        $athleteId = $this->db->prepare("SELECT AthleteID FROM FightAthletes WHERE FightID=?;");
                        $athleteId->execute([$fight['FightID']]);

                        if ($athleteId->rowCount() > 0) {
                            $athletes = $athleteId->fetchAll();

                            // create list of athlete id's so 1 query can retrieve them all
                            $athleteIdList = [];
                            foreach ($athletes as $athlete) {
                                array_push($athleteIdList, $athlete['AthleteID']);
                            }

                            $placeholders = str_repeat('?,', sizeof($athleteIdList) - 1) . '?';
                            $athleteQuery = "SELECT * FROM Athletes WHERE AthleteID IN ($placeholders);";
                            $athleteQuery = $this->db->prepare($athleteQuery);

                            $athleteQuery->execute($athleteIdList);

                            if ($athleteQuery->rowCount() > 0) {
                                $athletes = $athleteQuery->fetchAll();
                                $athleteData['Athletes'] = [];
                                // loop through athletes and add winner flag
                                foreach ($athletes as $athlete) {
                                    $athlete['Winner'] = ($fight['WinnerAthleteID'] == $athlete['AthleteID'] ? 1 : 0);
                                    array_push($athleteData['Athletes'], $athlete);
                                }
                                array_push($eventData['Fights'], array_merge($fight, $athleteData));
                            }
                        }
                    }
                }
                return $eventData;
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
     * @return array|false
     */
    public function getAll(int $limit = 5, int $start = 0)
    {

        $query = "SELECT 
                        E.* 
                    FROM 
                        Events E
                    ORDER BY 
                         EventDate DESC 
                    LIMIT $start, $limit";
        try {
            $query = $this->db->query($query);

            if ($query->rowCount() > 0) {
                $results = $query->fetchAll();
                $this->results = $results;

                $eventData = [];

                foreach ($results as $event) {
                    $athletesQuery = "SELECT 
                                        F.FightID,
                                        F.TitleBout,
                                        A.AthleteID,
                                        A.AthleteName,       
                                        A.AthleteImage,
                                        WC.WeightClass,
                                        IF(WC.WeightClass LIKE 'Women%', 1, 0) AS FemaleFight
                                    FROM 
                                        Fights F
                                    LEFT JOIN FightAthletes FA ON F.FightID = FA.FightID
                                    LEFT JOIN Athletes A ON FA.AthleteID = A.AthleteID
                                    LEFT JOIN WeightClasses WC ON F.WeightClassID = WC.WeightClassID
                                    INNER JOIN (SELECT FightID, COUNT(FightID) AS numAthletes FROM FightAthletes GROUP BY FightID) C ON F.FightID = C.FightID AND numAthletes = 2
                                    WHERE 
                                        F.EventID = ?
                                    ORDER BY F.FightID DESC
                                    LIMIT 2; ";

                    $athletesQuery = $this->db->prepare($athletesQuery);
                    $athletesQuery->execute([$event['EventID']]);

                    if ($athletesQuery->rowCount() > 0) {
                        $event['Headliners'] = $athletesQuery->fetchAll();
                    }

                    array_push($eventData, $event);
                }

                return $eventData;
            }

            return false;

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


    public function create(?array $data): int
    {
        if (!is_null($data)) {
            $this->processData($data);
        }

        $this->validateData();

        $query = "INSERT INTO Events (EventLocation, EventDate) VALUES (?, ?);";

        try {
            $query = $this->db->prepare($query);
            $query->execute([$this->location, $this->date]);

            $this->eventId = $this->db->lastInsertId();

            return $query->rowCount();
        } catch (PDOException | Exception $exception) {
            die($exception->getMessage());
        }
    }

    public function update(int $id, ?array $data = null): int
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
            $this->eventId = -1;
        } else {
            $this->eventId = $eventId;
        }
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