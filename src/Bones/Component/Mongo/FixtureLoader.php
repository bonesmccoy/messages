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
     * @param $configurationFilePath
     */
    public function __construct($configurationFilePath)
    {
        $this->parser = new Yaml();

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
            foreach (glob($fixturesPath . "/*yml") as $file) {
                $fixture = $this->parser->parse(file_get_contents($file));
                $fixtures = array_merge($fixtures, $fixture);
            }
        }

        $this->fixtures = $fixtures;
    }

    public function run()
    {
        $databaseConfiguration = $this->databaseConfiguration;

        $client = $this->createClient($databaseConfiguration);
        $this->insertFixtures($databaseConfiguration, $client);
    }

    /**
     * @param $databaseConfiguration
     * @return \MongoClient
     */
    private function createClient($databaseConfiguration)
    {
        $client = new \MongoClient(
            $databaseConfiguration->getConnectionUrl(),
            $databaseConfiguration->getConnectionOptions()
        );
        return $client;
    }

    /**
     * @param $databaseConfiguration
     * @param $client
     */
    private function insertFixtures($databaseConfiguration, $client)
    {
        foreach ($this->fixtures as $collection => $fixtures) {
            $dbName = $databaseConfiguration->getDatabaseName();
            $client->$dbName->$collection->remove(array());
            $client->$dbName->$collection->batchInsert($fixtures);
        }
    }


}
