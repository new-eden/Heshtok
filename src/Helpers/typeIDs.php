<?php

namespace Heshtok\Helpers;

use MongoDB\Database;

class typeIDs
{
    private $collectionName = "typeIDs";
    private $fileType = ".yaml";
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
    }

    public function insertData($workDir)
    {
        $className = (new \ReflectionClass(get_class()))->getShortName();
        echo "Now processing {$className} \n";

        echo "Adding Indexes\n";
        $this->addIndexes();

        $filePath = $workDir . "sde/fsd/{$className}{$this->fileType}";
        $fileData = file_get_contents($filePath);

        // PLEASE FOR FUCKS SAKE FIX YOUR STUPID SHIT
        $fileData = str_ireplace("\n\n\n'", "'", $fileData);
        $fileData = str_ireplace("\n\n'", "'", $fileData);

        echo "Processing Yaml\n";
        $array = yaml_parse($fileData);

        echo "Inserting data\n";
        foreach ($array as $key => $item) {
            try {
                $item["typeID"] = $key;
                $this->collection->insertOne($item);
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
                    "typeID" => 1
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
