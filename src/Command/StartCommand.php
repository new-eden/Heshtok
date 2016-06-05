<?php

namespace Heshtok\Command;

use Heshtok\Helpers\blueprints;
use Heshtok\Helpers\categoryIDs;
use Heshtok\Helpers\certificates;
use Heshtok\Helpers\graphicIDs;
use Heshtok\Helpers\groupIDs;
use Heshtok\Helpers\iconIDs;
use Heshtok\Helpers\landmarks;
use Heshtok\Helpers\skinLicenses;
use Heshtok\Helpers\skinMaterials;
use Heshtok\Helpers\skins;
use Heshtok\Helpers\tournamentRuleSets;
use Heshtok\Helpers\typeIDs;
use MongoDB\Client;
use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class StartCommand extends Command{
    protected function configure() {
        $this->setName("start")
            ->setDescription("Start the conversion of the latest YAML Database Dump from CCP, to MongoDB")
            ->addOption("databaseName", null, InputOption::VALUE_OPTIONAL, "The name of the database to store the tables in", "ccp")
            ->addOption("workdir", null, InputOption::VALUE_OPTIONAL, "The directory where we will store temporary files", "/tmp/");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
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
        $url = "https://cdn1.eveonline.com/data/sde/tranquility/sde-20160531-TRANQUILITY.zip";
        $fileName = basename($url);

        // @todo use a proper package/function/whatever for this part
        if(!file_exists($workDir . $fileName)) {
            exec("curl --progress-bar -o {$workDir}{$fileName} {$url}");
            exec("unzip {$workDir}{$fileName}");
        }

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

        $universeEve;
        $universeWormhole;
    }
}