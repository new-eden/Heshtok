<?php

namespace Heshtok\Command;

use Heshtok\Helpers\blueprints;
use Heshtok\Helpers\categoryIDs;
use Heshtok\Helpers\certificates;
use Heshtok\Helpers\graphicIDs;
use Heshtok\Helpers\groupIDs;
use Heshtok\Helpers\iconIDs;
use Heshtok\Helpers\invFlags;
use Heshtok\Helpers\landmarks;
use Heshtok\Helpers\skinLicenses;
use Heshtok\Helpers\skinMaterials;
use Heshtok\Helpers\skins;
use Heshtok\Helpers\tournamentRuleSets;
use Heshtok\Helpers\typeIDs;
use Heshtok\Helpers\universeCelestials;
use Heshtok\Helpers\universeConstellations;
use Heshtok\Helpers\universeRegions;
use Heshtok\Helpers\universeSystems;
use MongoDB\Client;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class StartCommand extends Command
{
    protected function configure()
    {
        $this->setName("start")
            ->setDescription("Start the conversion of the latest YAML Database Dump from CCP, to MongoDB")
            ->addOption("databaseName", null, InputOption::VALUE_OPTIONAL, "The name of the database to store the tables in", "ccp")
            ->addOption("workdir", null, InputOption::VALUE_OPTIONAL, "The directory where we will store temporary files", "/tmp/");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set("memory_limit", "-1");
        error_reporting(1);
        error_reporting(E_ALL);

        // Initialize MongoDB
        $mongoInit = new Client();
        /** @var Database $mongo */
        $mongo = $mongoInit->selectDatabase($input->getOption("databaseName"));

        // Workdirs
        $workDir = $input->getOption("workdir");
        chdir($workDir);

        // @todo once CCP moves to latest.zip / latest.md5 switch to that, and use the md5 to check if the currently installed version is the latest
        // Download the Latest zip file (Currently hardcoded, because CCP havn't added a latest/md5 yet.. *sigh*)
        $url = "https://cdn1.eveonline.com/data/sde/tranquility/sde-20160809-TRANQUILITY.zip";
        $sqlite = "https://www.fuzzwork.co.uk/dump/sqlite-latest.sqlite.bz2";

        echo "Downloading and unpacking the CCP SDE\n";
        $fileName = basename($url);
        exec("curl --progress-bar -o {$workDir}{$fileName} {$url}");
        exec("unzip {$workDir}{$fileName}");

        echo "Downloading and unpacking the SQLite Dump from Fuzzysteve\n";
        $sqliteBaseName = basename($sqlite);
        exec("curl --progress-bar -o {$workDir}{$sqliteBaseName} {$sqlite}");
        exec("bzip2 -d {$workDir}{$sqliteBaseName}");

        $sqliteFile = $workDir . str_replace(".bz2", "", $sqliteBaseName);

        // Start processing
        $blueprints = new blueprints($mongo);
        $blueprints->insertData($workDir);

        $categoryIDs = new categoryIDs($mongo);
        $categoryIDs->insertData($workDir);

        $certificates = new certificates($mongo);
        $certificates->insertData($workDir);

        $graphicIDs = new graphicIDs($mongo);
        $graphicIDs->insertData($workDir);

        $groupIDs = new groupIDs($mongo);
        $groupIDs->insertData($workDir);

        $iconIDs = new iconIDs($mongo);
        $iconIDs->insertData($workDir);

        $landmarks = new landmarks($mongo);
        $landmarks->insertData($workDir);

        $skinLicenses = new skinLicenses($mongo);
        $skinLicenses->insertData($workDir);

        $skinMaterials = new skinMaterials($mongo);
        $skinMaterials->insertData($workDir);

        $skins = new skins($mongo);
        $skins->insertData($workDir);

        $tournamentRuleSets = new tournamentRuleSets($mongo);
        $tournamentRuleSets->insertData($workDir);

        $typeIDs = new typeIDs($mongo);
        $typeIDs->insertData($workDir);

        $eveSystems = new universeSystems($mongo);
        $eveSystems->insertData($workDir);

        $eveConstellations = new universeConstellations($mongo);
        $eveConstellations->insertData($workDir);

        $eveRegions = new universeRegions($mongo);
        $eveRegions->insertData($workDir);

        $eveCelestials = new universeCelestials($mongo);
        $eveCelestials->insertData($sqliteFile);
        
        $invFlags = new invFlags($mongo);
        $invFlags->insertData($sqliteFile);
        

        // Clean up delete downloaded file, and remove sde library
        exec("rm {$workDir}{$fileName}");
        exec("rm -R {$workDir}/sde");
        exec("rm -R {$sqliteFile}");
    }
}
