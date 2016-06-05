<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class graphicIDs {
    private $collectionName = "graphicIDs";
    private $fileType = ".yaml";
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

        $filePath = $workDir . "sde/fsd/{$className}{$this->fileType}";
        $fileData = file_get_contents($filePath);

        $fileData = str_replace("description: ", "description: |\n    ", $fileData);
        
        echo "Processing Yaml\n";
        $array = yaml_parse($fileData);

        echo "Inserting data\n";
        foreach ($array as $key => $item) {
            try {
                $item["graphicsID"] = $key;
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
                    "graphicsID" => 1
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