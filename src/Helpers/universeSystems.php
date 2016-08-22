<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class universeSystems
{
    private $collectionName = "solarSystems";
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
        $locations = $workDir . "sde/fsd/universe/*/*/*/*/*{$this->fileType}";
        $files = glob($locations);
        $yaml = new Yaml();

        foreach ($files as $file) {
            $exp = explode("/", $file);
            $region = $exp[6];
            $constellation = $exp[7];
            $systemName = $exp[8];
            $data = $yaml::parse(file_get_contents($file));
            $regionData = $yaml::parse(file_get_contents($workDir . "sde/fsd/universe/{$exp[5]}/{$region}/region.staticdata"));
            $constellationData = $yaml::parse(file_get_contents($workDir . "sde/fsd/universe/{$exp[5]}/{$region}/{$constellation}/constellation.staticdata"));;
            $data["regionID"] = $regionData["regionID"];
            $data["regionName"] = $region;
            $data["constellationID"] = $constellationData["constellationID"];
            $data["constellationName"] = $constellation;
            $data["solarSystemName"] = $systemName;

            ksort($data);

            echo "inserting: {$data["solarSystemName"]}\n";
            $this->collection->insertOne($data, array("upsert" => true));
        }
    }

    private function addIndexes()
    {
        try {
            $this->collection->createIndex(
                array(
                    "solarSystemID" => -1
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
                array("constellationID" => -1)
            );
            $this->collection->createIndex(
                array("solarSystemName" => -1)
            );
            $this->collection->createIndex(
                array("solarSystemName" => "text")
            );

        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}