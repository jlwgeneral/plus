<?php

declare(strict_types=1);

/*
 * +----------------------------------------------------------------------+
 * |                          ThinkSNS Plus                               |
 * +----------------------------------------------------------------------+
 * | Copyright (c) 2018 Chengdu ZhiYiChuangXiang Technology Co., Ltd.     |
 * +----------------------------------------------------------------------+
 * | This source file is subject to version 2.0 of the Apache license,    |
 * | that is bundled with this package in the file LICENSE, and is        |
 * | available through the world-wide-web at the following url:           |
 * | http://www.apache.org/licenses/LICENSE-2.0.html                      |
 * +----------------------------------------------------------------------+
 * | Author: Slim Kit Group <master@zhiyicx.com>                          |
 * | Homepage: www.thinksns.com                                           |
 * +----------------------------------------------------------------------+
 */

namespace Zhiyi\Plus\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;

class PackageLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'package:link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "repositorie" to "vendor" (Composer component.)';

    /**
     * The console command filesystem.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Perform the operation.
     *
     * @return void
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function handle()
    {
        $this->warn('The command be removed soon.');
        $package = $this->getPackageInput();

        $target = $this->getSourceDir($package);
        $link = $this->getComponentVendorDir($package);
        $parentDir = dirname($link);

        if (! $this->filesystem->isDirectory($parentDir)) {
            $this->filesystem->makeDirectory($parentDir);
        }

        // delete vendor dir.
        if (! $this->filesystem->delete($link)) {
            $this->filesystem->deleteDirectory($link, false);
        }

        // create link.
        $this->filesystem->link($target, $link);

        $this->info("The [{$target}] has been linked to [{$link}]");
    }

    /**
     * Get the installation directory and store the directory where the vendor is located.
     *
     * @param string $package
     * @return string
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function getComponentVendorDir(string $package): string
    {
        $baseVendor = array_get($this->getRootComposer(), 'config.vendor-dir', 'vendor');
        $componentName = array_get($this->getComponentComposer($package), 'name');

        return base_path($baseVendor.'/'.$componentName);
    }

    /**
     * Get root package information.
     *
     * @return array
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function getRootComposer(): array
    {
        static $composer;

        if (! $composer) {
            $composerFile = base_path('composer.json');
            $composerJson = $this->filesystem->get($composerFile);
            $composer = json_decode($composerJson, true);
        }

        return $composer;
    }

    /**
     * Get package composer information.
     *
     * @param string $package
     * @return array
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function getComponentComposer(string $package): array
    {
        static $composer;

        if (! $composer) {
            $composerFile = $this->getSourceDir($package).'/composer.json';
            $composerJson = $this->filesystem->get($composerFile);
            $composer = json_decode($composerJson, true);
        }

        return $composer;
    }

    /**
     * Get the package dir.
     *
     * @param string $package
     * @return component dir
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function getSourceDir(string $package): string
    {
        $sourceDir = resource_path('repositorie/sources/'.$package);

        if (! $this->filesystem->exists($sourceDir.'/composer.json')) {
            throw new \Exception('The package is not "composer.json" exist!');
        }

        return $sourceDir;
    }

    /**
     * Get the command [package].
     *
     * @return string
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function getPackageInput(): string
    {
        if (! $this->hasArgument('package')) {
            throw new \Exception('"package" already exists!');
        }

        return str_replace('/', '-', $this->argument('package'));
    }

    /**
     * get command arguments.
     *
     * @return array
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    protected function getArguments()
    {
        return [
            ['package', InputArgument::REQUIRED, 'The package to symbolic link instead by dir.'],
        ];
    }
}
