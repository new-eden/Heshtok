<?php

namespace Heshtok\Helpers;

use MongoDB\Database;

class universeConstellations
{
    private $collectionName = "constellations";
    private $fileType = ".staticdata";
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

    public function insertData($workDir)
    {
        $className = (new \ReflectionClass(get_class()))->getShortName();
        echo "Now processing {$className} \n";

        echo "Adding Indexes\n";
        $this->addIndexes();

        echo "Importing data\n";
        // Find all the .staticdata files
        $locations = $workDir . "sde/fsd/universe/*/*/*/*{$this->fileType}";
        $files = glob($locations);

        foreach ($files as $file) {
            $exp = explode("/", $file);
            $region = $exp[6];
            $constellation = $exp[7];
            $data = yaml_parse(file_get_contents($file));
            $regionData = yaml_parse(file_get_contents($workDir . "sde/fsd/universe/{$exp[5]}/{$region}/region.staticdata"));
            $data["regionID"] = $regionData["regionID"];
            $data["regionName"] = $region;
            $data["constellationName"] = $constellation;

            ksort($data);
            $this->collection->insertOne($data, array("upsert" => true));
        }
    }

    private function addIndexes()
    {
        try {
            $this->collection->createIndex(
                array(
                    "constellationID" => -1
                ),
                array(
                    "unique" => 1
                )
            );
            $this->collection->createIndex(
                array("regionName" => -1)
            );
            $this->collection->createIndex(
                array("regionID" => -1)
            );
            $this->collection->createIndex(
                array("constellationName" => -1)
            );
            $this->collection->createIndex(
                array("constellationName" => "text")
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}