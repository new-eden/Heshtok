<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class landmarks {
    private $collectionName = "landmarks";
    private $fileType = ".staticdata";
    private $collection;
    public function __construct(Database $mongoDB) {
        // Try and make the collection first
        try {
            $mongoDB->createCollection($this->collectionName);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }

        $this->collection = $mongoDB->selectCollection($this->collectionName);
    }

    public function insertData($workDir) {
        $className = (new \ReflectionClass(get_class()))->getShortName();
        echo "Now processing {$className} \n";

        echo "Adding Indexes\n";
        $this->addIndexes();

        $filePath = $workDir . "sde/fsd/landmarks/{$className}{$this->fileType}";
        $fileData = file_get_contents($filePath);

        echo "Processing Yaml\n";
        $array = Yaml::parse($fileData);

        echo "Inserting data\n";
        foreach ($array as $key => $item) {
            try {
                $item["landmarkID"] = $key;
                $this->collection->insertOne($item);
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
            }
        }
    }

    private function addIndexes() {
        try {
            $this->collection->createIndex(
                array(
                    "landmarkID" => 1
                ),
                array(
                    "unique" => 1
                )
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}