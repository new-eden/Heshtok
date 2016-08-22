<?php

namespace Heshtok\Helpers;

use MongoDB\Database;
use Symfony\Component\Yaml\Yaml;

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
        $this->collection->deleteMany(array());
        $this->collection->dropIndexes();
    }

    public function insertData($workDir)
    {
        $className = (new \ReflectionClass(get_class()))->getShortName();
        echo "Now processing {$className} \n";

        echo "Adding Indexes\n";
        $this->addIndexes();

        $filePath = $workDir . "sde/fsd/{$className}{$this->fileType}";

        // This is just ridiculous...
        $d = file_get_contents($filePath);
        $d = str_ireplace("\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n'", "'", $d);
        $d = str_ireplace("\n\n\n\n\n'", "'", $d);
        $d = str_ireplace("\n\n\n\n'", "'", $d);
        $d = str_ireplace("\n\n\n'", "'", $d);
        $d = str_ireplace("\n\n'", "'", $d);
        file_put_contents("{$workDir}tmpfile.yaml", $d);

        $fileData = file($workDir . "tmpfile.yaml");

        // Move all the languages down a line with an > \n tacked behind it - to denote multi-line strings..
        $fix = array("  de:", "  en:", "  fr:", "  ja:", "  ru:", "  zh:", "  es:", "  it:");
        $line = 0;
        $yamlData = "";
        foreach($fileData as $string) {
            $lang = false;

            foreach($fix as $l) {
                if (stristr($string, $l)) {
                    $explode = explode($l, $string);
                    $space = $explode[0] . "    ";
                    $lang = true;
                    $yamlData .= str_replace($l, "{$l} > \n{$space}", $string);
                }
            }

            if($lang == false)
                $yamlData .= $string;

            $line++;
        }


        echo "Processing Yaml\n";
        $yaml = new Yaml();
        $array = $yaml::parse($yamlData);

        echo "Inserting data\n";
        foreach ($array as $key => $item) {
            try {
                $item["typeID"] = $key;
                $this->collection->insertOne($item, array("upsert" => true));
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
                    "typeID" => -1
                ),
                array(
                    "unique" => 1
                )
            );
            $this->collection->createIndex(
                array(
                    "\$**" => "text"
                )
            );
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}
