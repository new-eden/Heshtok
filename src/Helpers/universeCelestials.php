<?php
/**
 * Created by PhpStorm.
 * User: micha
 * Date: 13-07-2016
 * Time: 01:48
 */

namespace Heshtok\Helpers;


use MongoDB\Database;

class universeCelestials
{
    private $collectionName = "celestials";
    private $collection;

    public function __construct(Database $mongoDB)
    {
        // Try and make the collection first
        try {
            $mongoDB->createCollection($this->collectionName);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        $this->collection = $mongoDB->selectCollection($this->collectionName);
        $this->collection->deleteMany(array());
        $this->collection->dropIndexes();
    }

    public function insertData($sqlite)
    {
        $className = (new \ReflectionClass(get_class()))->getShortName();
        echo "Now processing {$className} \n";

        echo "Adding Indexes\n";
        $this->addIndexes();

        echo "Opening Sqlite connection and executing query\n";
        // Open a connection to the SQLite file
        $db = new \PDO("sqlite:" . $sqlite);
        $query = "select `mapDenormalize`.`itemID` AS `itemID`,`mapDenormalize`.`itemName` AS `itemName`,`invTypes`.`typeName` AS `typeName`,`mapDenormalize`.`typeID` AS `typeID`,`mapSolarSystems`.`solarSystemName` AS `solarSystemName`,`mapDenormalize`.`solarSystemID` AS `solarSystemID`,`mapDenormalize`.`constellationID` AS `constellationID`,`mapDenormalize`.`regionID` AS `regionID`,`mapRegions`.`regionName` AS `regionName`,`mapDenormalize`.`orbitID` AS `orbitID`,`mapDenormalize`.`x` AS `x`,`mapDenormalize`.`y` AS `y`,`mapDenormalize`.`z` AS `z` from ((((`mapDenormalize` join `invTypes` on((`mapDenormalize`.`typeID` = `invTypes`.`typeID`))) join `mapSolarSystems` on((`mapSolarSystems`.`solarSystemID` = `mapDenormalize`.`solarSystemID`))) join `mapRegions` on((`mapDenormalize`.`regionID` = `mapRegions`.`regionID`))) join `mapConstellations` on((`mapDenormalize`.`constellationID` = `mapConstellations`.`constellationID`)))";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        echo "Inserting data\n";
        foreach($result as $cel) {
            try {
                $this->collection->insertOne($cel, array("upsert" => true));
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
            }
        }
    }

    private function addIndexes()
    {
        try {
            $this->collection->createIndex(
                array(
                    "itemID" => -1
                ),
                array(
                    "unique" => 1
                )
            );
            
            $this->collection->createIndex(
                array(
                    "itemName" => -1
                )
            );
            $this->collection->createIndex(
                array(
                    "x" => -1
                )
            );
            $this->collection->createIndex(
                array(
                    "y" => -1
                )
            );
            $this->collection->createIndex(
                array(
                    "z" => -1
                )
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}