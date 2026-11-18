<?php

use Robo\Symfony\ConsoleIO;
use Symfony\Component\Finder\Finder;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends \Robo\Tasks {
    protected $workspace_dir = 'tests/integration/workspace';

    public function clean() {
        $collection = $this->collectionBuilder();

        $collection->taskFilesystemStack()
            ->remove($this->workspace_dir . '/.gitignore')
            ->run();
        $collection->taskCleanDir([$this->workspace_dir])->run();

        return $collection->run();
    }

    public function test(ConsoleIO $io, $version = 'dev-master') {
        $composer_json = 'tests/integration/composer.site.json';

        $collection = $this->collectionBuilder();

        // 1. Clean workspace directory
        $this->clean();

        // 2. Install base SimpleID
        $collection->taskComposerCreateProject()
            ->source('simpleid/simpleid:' . $version)
            ->target($this->workspace_dir)
            ->noInstall(true)
            ->noDev()
            ->run();

        // 3. Update composer.json to add local repository
        $collection->taskFilesystemStack()
            ->copy($composer_json, $this->workspace_dir . '/composer.site.json')
            ->run();

        // 4. Update minimum-stability in root composer.json
        $collection->taskComposerConfig()
            ->workingDir($this->workspace_dir)
            ->set('minimum-stability', 'dev')
            ->run();
        $collection->taskComposerConfig()
            ->workingDir($this->workspace_dir)
            ->set('prefer-stable', 'true')
            ->run();

        // 5. Try installing the test module
        $collection->taskComposerUpdate()
            ->workingDir($this->workspace_dir)
            ->noDev()
            ->run();

        // 6. See if module exists in the right location and is part of
        //    autoload
        $collection->addCode(function() use ($io) {
            $expected_file = $this->workspace_dir . '/www/site/test/src/TestModule/TestModule.php';
            if (!file_exists($expected_file)) {
                $io->error('Test module not installed');
                return 1;
            } else {
                $io->success('Test module installed in expected location');
            }
        });

        $collection->addCode(function() use ($io) {
            $autoload_file = $this->workspace_dir . '/www/vendor/composer/autoload_psr4.php';
            $autoloads = include($autoload_file);
            if (!array_key_exists('SimpleID\\Modules\\TestModule\\', $autoloads)) {
                $io->error('Test module not included in autoload');
                return 1;
            } else {
                $io->success('Test module included in autoload');
            }
        });

        return $collection->run();
    }

    public function update_copyright() {
        $current_year = strftime("%Y");

        $finder = new Finder();
        $finder->in(['src'])->name('*.php');

        foreach($finder as $file) {
            $this->taskReplaceInFile($file)
                ->regex('/Copyright \(C\) Kelvin Mo (\d{4})-(\d{4})(\R)/m')
                ->to('Copyright (C) Kelvin Mo $1-'. $current_year . '$3')
                ->run();
            $this->taskReplaceInFile($file)
                ->regex('/Copyright \(C\) Kelvin Mo (\d{4})(\R)/m')
                ->to('Copyright (C) Kelvin Mo $1-'. $current_year . '$2')
                ->run();
        }
    }
}