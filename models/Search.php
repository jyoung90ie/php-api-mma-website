<?php


namespace models;

/**
 * Class Search
 * @package models
 */
class Search
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Returns array of athletes that partially match the search term provided.
     *
     * @param array $data array containing searchTerm value
     * @return mixed results array if search term was found, otherwise, returns false.
     */
    public function searchByAthleteName(array $data)
    {
        $query = "SELECT 
                        A.*,
                        LastFight.*,
                        WC.WeightClass,
                        Total.TotalFights,
                        E.EventDate,
                        E.EventLocation
                    FROM  
                        Athletes A
                        LEFT JOIN FightAthletes FA on FA.`AthleteID` = A.`AthleteID` AND FA.`FightID` = (SELECT MAX(FightID) FROM FightAthletes WHERE AthleteID = FA.AthleteID)
                        LEFT JOIN Fights AS LastFight ON LastFight.FightID = FA.FightID    
                        LEFT JOIN 
                            (SELECT COUNT(*) AS TotalFights, AthleteID FROM FightAthletes GROUP BY AthleteID) Total
                            ON Total.AthleteID = A.AthleteID
                        LEFT JOIN WeightClasses WC on LastFight.WeightClassID = WC.WeightClassID
                        LEFT JOIN Events E on LastFight.EventID = E.EventID

                    WHERE A.AthleteName LIKE :athleteName
                    ";
        $searchTerm = $data['searchTerm'];
        $params = [':athleteName' => '%' . $searchTerm . '%'];

        $query = $this->db->prepare($query);
        $query->execute($params);

        $rowCount = $query->rowCount();

        if ($rowCount > 0) {
            return $query->fetchAll();
        }

        return false;
    }

}
