<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class universeRegions
{
    private $collectionName = "regions";
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
        $locations = $workDir . "sde/fsd/universe/*/*/*{$this->fileType}";
        $files = glob($locations);
        $yaml = new Yaml();
        foreach ($files as $file) {
            $exp = explode("/", $file);
            $region = $exp[6];
            $data = $yaml::parse(file_get_contents($file));
            $data["regionName"] = $region;

            ksort($data);
            $this->collection->insertOne($data, array("upsert" => true));
        }
    }

    private function addIndexes()
    {
        try {
            $this->collection->createIndex(
                array(
                    "regionID" => -1
                ),
                array(
                    "unique" => 1
                )
            );
            $this->collection->createIndex(
                array("regionName" => 1)
            );
            $this->collection->createIndex(
                array("regionName" => "text")
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}