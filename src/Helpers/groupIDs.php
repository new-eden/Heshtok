<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

class groupIDs {
    private $collectionName = "groupIDs";
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

        $fileData = str_replace("de: ", "de: > \n            ", $fileData);
        $fileData = str_replace("en: ", "en: > \n            ", $fileData);
        $fileData = str_replace("fr: ", "fr: > \n            ", $fileData);
        $fileData = str_replace("ja: ", "ja: > \n            ", $fileData);
        $fileData = str_replace("ru: ", "ru: > \n            ", $fileData);
        $fileData = str_replace("zh: ", "zh: > \n            ", $fileData);

        echo "Processing Yaml\n";
        $array = yaml_parse($fileData);

        echo "Inserting data\n";
        foreach ($array as $key => $item) {
            try {
                $item["groupID"] = $key;
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
                    "groupID" => 1
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