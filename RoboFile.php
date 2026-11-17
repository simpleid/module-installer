<?php

use Symfony\Component\Finder\Finder;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends \Robo\Tasks {
    public function test() {
        $workspace_dir = 'tests/integration/workspace';
        $composer_json = 'tests/integration/composer.site.json';

        $collection = $this->collectionBuilder();

        // 1. Clean workspace directory
        $collection->taskFilesystemStack()
            ->remove($workspace_dir . '/.gitignore')
            ->run();
        $collection->taskCleanDir([$workspace_dir])->run();

        // 2. Install base SimpleID
        $collection->taskComposerCreateProject()
            ->source('simpleid/simpleid:dev-master')
            ->target($workspace_dir)
            ->noInstall(true)
            ->noDev()
            ->run();

        // 3. Update composer.json to add local repository
        $collection->taskFilesystemStack()
            ->copy($composer_json, $workspace_dir . '/composer.site.json')
            ->run();

        // 4. Update minimum-stability in root composer.json
        $collection->taskComposerConfig()
            ->workingDir($workspace_dir)
            ->set('minimum-stability', 'dev')
            ->run();
        $collection->taskComposerConfig()
            ->workingDir($workspace_dir)
            ->set('prefer-stable', 'true')
            ->run();

        // 5. Try installing the test module
        $collection->taskComposerUpdate()
            ->workingDir($workspace_dir)
            ->noDev()
            ->run();

        // 6. See if module exists in the right location
        $collection->addCode(function() use ($workspace_dir) {
            $expected_file = $workspace_dir . '/www/site/test/src/TestModule/TestModule.php';
            if (!file_exists($expected_file)) return 1;
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