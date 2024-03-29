<?php

namespace models;

use InvalidArgumentException;

/**
 * Class Event
 * @package models
 */
class Event
{
    const DATE_MIN = '1993-11-12'; // date of first ever event
    const PERMISSION_AREA = 'EVENTS';

    private $eventId = null;
    private $location = null;
    private $date = null;
    private $results = null;

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns data for specified event, including fights - note fights are NOT paginated.
     *
     * @param int $eventId the event id
     * @return array|false single event with list of fights
     */
    public function getOne(int $eventId)
    {
        // performs validation checks before setting
        $this->setEventId($eventId);

        $query = "SELECT * FROM Events WHERE EventID = ?";

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
                                FR.WinnerAthleteID,
                                RT.ResultDescription,
                                FR.WinRound,
                                FR.WinRoundTime
                            FROM Fights F 
                            LEFT JOIN WeightClasses WC on F.WeightClassID = WC.WeightClassID
                            LEFT JOIN Referees R on F.RefereeID = R.RefereeID
                            LEFT JOIN FightResults FR on F.FightID = FR.FightID
                            LEFT JOIN ResultTypes RT on FR.ResultTypeID = RT.ResultTypeID
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
    }

    /**
     * Return list of events in descending order by event date - results are paginated.
     *
     * @param int $limit the number of events to return
     * @param int $start the event to start from
     * @return array|false list of events with athlete data
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

    /**
     * Create a new event entry in the database
     *
     * @param array|null $data should contain EventLocation, EventDate
     * @return int number of records created
     */
    public function create(array $data): int
    {
        $this->processData($data);
        $this->validateData();

        $query = "INSERT INTO Events (EventLocation, EventDate) VALUES (?, ?);";

        $query = $this->db->prepare($query);
        $query->execute([$this->location, $this->date]);

        $this->eventId = $this->db->lastInsertId();

        return $query->rowCount();

    }

    /**
     * Updates database record for the specified event.
     *
     * @param int $id the event id
     * @param array|null $data should contain EventLocation, EventDate
     * @return int number of records updated
     */
    public function update(int $id, array $data): int
    {
        $this->setEventId($id);
        $this->processData($data);

        $this->validateData();

        $query = "UPDATE Events 
                    SET 
                        EventLocation = ?, 
                        EventDate = ?
                    WHERE 
                        EventID = ?";

        $query = $this->db->prepare($query);
        $query->execute([$this->location, $this->date, $this->eventId]);

        return $query->rowCount();
    }

    /**
     * Delete the specified record from the database.
     *
     * @param int $id of the record to be deleted
     * @return int the number of rows deleted
     */
    public function delete(int $id): int
    {
        $this->setEventId($id);

        $query = "DELETE FROM Events WHERE EventID = ?";


        $query = $this->db->prepare($query);
        $query->execute([$this->eventId]);

        return $query->rowCount();
    }

    /**
     * Extracts inputs from data array and calls setters. If any data is not in the expected format
     * exceptions will be thrown from the relevant setter.
     *
     * @param array $data
     */
    private function processData(array $data): void
    {
        $this->setDate($data['EventDate'] ?? '');
        $this->setLocation($data['EventLocation'] ?? '');
    }

    /**
     * Checks that all record fields have been populated. If not, throws InvalidArgumentException.
     */
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
            throw new InvalidArgumentException("Invalid value for EventID.");
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
        if (empty($location)) {
            throw new InvalidArgumentException('Invalid value for EventLocation.');
        }
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