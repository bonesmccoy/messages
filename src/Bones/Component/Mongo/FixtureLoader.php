<?php


namespace Bones\Component\Mongo;


use Symfony\Component\Yaml\Yaml;

class FixtureLoader
{
    /**
     * @var Yaml
     */
    protected $parser;

    /**
     * @var DatabaseConfiguration
     */
    protected $databaseConfiguration;

    /**
     * @var array
     */
    protected $fixtures = array();

    /**
     * @var string
     */
    protected $appRoot;

    /**
     * @param $appRoot
     * @param $configurationFilePath
     */
    public function __construct($appRoot, $configurationFilePath)
    {
        $this->parser = new Yaml();
        $this->appRoot = $appRoot;

        if (!file_exists($configurationFilePath)) {
            throw new \InvalidArgumentException("File $configurationFilePath doesn't exists");
        }

        $configuration = $this->parser->parse(file_get_contents($configurationFilePath));
        $this->loadDatabaseConfiguration($configuration['config']);
        $this->parseFixturesFiles($configuration['config']);


    }

    /**
     * @param $configuration
     */
    private function loadDatabaseConfiguration($configuration)
    {
        $this->databaseConfiguration = new DatabaseConfiguration($configuration);
    }

    private function parseFixturesFiles($configuration)
    {
        if (!isset($configuration['fixtures']['paths'])) {
            throw new \InvalidArgumentException("Fixture configuration not found");
        }

        $fixtures = array();
        foreach ($configuration['fixtures']['paths'] as $fixturesPath) {
            foreach (glob($this->appRoot . "/" . $fixturesPath . "/*yml") as $file) {
                $fixture = $this->parser->parse(file_get_contents($file));
                $fixtures = array_merge($fixtures, $fixture);
            }
        }

        $this->fixtures = $fixtures;
    }

    public function run()
    {
        $client = $this->createClient();
        $this->insertFixtures($client);
    }

    /**
     * @return \MongoClient
     */
    private function createClient()
    {
        $client = new \MongoClient(
            $this->databaseConfiguration->getConnectionUrl(),
            $this->databaseConfiguration->getConnectionOptions()
        );

        return $client;
    }

    /**
     * @param \MongoClient $client
     */
    private function insertFixtures($client)
    {
       $databaseName = $this->databaseConfiguration->getDatabaseName();
        foreach ($this->fixtures as $collection => $fixtures) {
            echo sprintf("Adding %s fixture to the collection %s\n",
                count($fixtures),
                $collection);
            $client->$databaseName->$collection->remove(array());
            $client->$databaseName->$collection->batchInsert($fixtures);
        }
    }


}
