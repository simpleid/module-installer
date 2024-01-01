<?php
/*
 * SimpleID
 *
 * Copyright (C) Kelvin Mo 2015-2024
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public
 * License along with this program; if not, write to the Free
 * Software Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 */

namespace SimpleID\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class ModuleInstallerPlugin implements PluginInterface {

    protected $installer;

    public function activate(Composer $composer, IOInterface $io) {
        $this->installer = new ModuleInstaller($io, $composer);
        $composer->getInstallationManager()->addInstaller($this->installer);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io) {
        $composer->getInstallationManager->removeInstaller($this->installer);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io) {
    }
}

?>
