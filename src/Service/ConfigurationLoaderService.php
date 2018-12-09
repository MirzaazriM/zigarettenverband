<?php

namespace App\Service;

use Symfony\Component\Yaml\Yaml;

class ConfigurationLoaderService
{

    private $configuration;

    public function __construct(string $path)
    {
        // load configuration data
        $this->configuration = Yaml::parse(file_get_contents($path));
    }


    /**
     * Get developer info
     *
     * @return mixed
     */
    public function getDeveloperInfo() {
        // extract developer_info data
        $config = $this->configuration['developer_info'];

        // return data
        return $config;
    }


    /**
     * Get database info
     *
     * @return mixed
     */
    public function getDatabaseInfo() {
        // extract database_info data
        $config = $this->configuration['database_info'];

        // return data
        return $config;
    }

}