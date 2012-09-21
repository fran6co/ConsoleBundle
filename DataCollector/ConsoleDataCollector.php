<?php

/*
 * This file is part of the SncRedisBundle package.
 *
 * (c) Henrik Westphal <henrik.westphal@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sf2gen\Bundle\ConsoleBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class ConsoleDataCollector extends DataCollector
{
    protected $kernel;
    protected $cacheDir;

    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        $this->cacheDir = $this->kernel->getCacheDir() . DIRECTORY_SEPARATOR . 'sf2genconsole' . DIRECTORY_SEPARATOR;
    }

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $commands = $this->getCacheContent();

        if ($commands === false) {
            if (!is_dir($this->cacheDir)) {
                mkdir($this->cacheDir, 0777);
            }

            $commands = $this->fetchCommands();

            file_put_contents($this->cacheDir . 'commands.json', json_encode($commands));
        } else {
            $commands = json_decode($commands);
        }

        $this->data = array('commands' => $commands);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'console';
    }

    public function getCommands()
    {
        return $this->data['commands'];
    }

    protected function fetchCommands()
    {
        $application = new Application($this->kernel);
        foreach ($this->kernel->getBundles() as $bundle) {
            $bundle->registerCommands($application);
        }

        return array_keys($application->all());
    }

    protected function getCacheContent()
    {
        if (is_file($this->cacheDir . 'commands.json')){
            return file_get_contents($this->cacheDir . 'commands.json');
        }

        return false;
    }
}
