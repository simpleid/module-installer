<?php

use Symfony\Component\Finder\Finder;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see https://robo.li/
 */
class RoboFile extends \Robo\Tasks {
    public function tests() {
        $workspace_dir = 'tests/integration/workspace';
        $composer_json = 'tests/integration/composer.site.json';

        $collection = $this->collectionBuilder();

        // 1. Clean workspace directory
        $collection->taskDeleteDir([$workspace_dir])->run();

        // 2. Install base SimpleID
        $collection->taskComposerCreateProject()
            ->source('simpleid/simpleid:dev-master')
            ->target($workspace_dir)
            ->noDev()
            ->run();

        // 3. Update composer.json to add local repository
        $collection->taskFilesystemStack()
            ->copy($composer_json, $workspace_dir . '/composer.site.json')
            ->run();

        // 4. Update minimum-stability in root composer.json
        $collection->addCode(function() use ($workspace_dir) {
            $root_composer_json = $workspace_dir . '/composer.json';
            $root_composer = json_decode(file_get_contents($root_composer_json), true);
            $root_composer['minimum-stability'] = 'dev';
            $root_composer['prefer-stable'] = true;
            file_put_contents($root_composer_json, json_encode($root_composer));
        });

        // 5. Try installing the test module
        $collection->taskComposerUpdate()
            ->workingDir($workspace_dir)
            ->noDev()
            ->run();

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