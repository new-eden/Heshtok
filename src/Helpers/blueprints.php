<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class blueprints {
    private $collectionName = "blueprints";
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

        echo "Processing Yaml\n";
        $array = Yaml::parse($fileData);

        echo "Inserting data\n";
        foreach ($array as $item) {
            try {
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
                    "blueprintTypeID" => 1
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