<?php
/**
 * Created by PhpStorm.
 * User: micha
 * Date: 13-07-2016
 * Time: 01:48
 */

namespace Heshtok\Helpers;


use MongoDB\Database;

class invFlags
{
    private $collectionName = "invFlags";
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
        $query = "select * from invFlags";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        echo "Inserting data\n";
        foreach($result as $cel) {
            try {
                $d = array(
                    "flagID" => (int) $cel["flagID"],
                    "flagName" => $cel["flagName"],
                    "flagText" => $cel["flagText"],
                    "orderID" => (int) $cel["orderID"]
                );
                $this->collection->insertOne($d, array("upsert" => true));
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
                    "flagID" => -1
                ),
                array(
                    "unique" => 1
                )
            );
            
            $this->collection->createIndex(
                array(
                    "flagName" => -1
                )
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}