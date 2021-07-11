<?php

declare(strict_types=1);

namespace TmpApp\Console;

use Dimsh\React\Filesystem\Monitor\Monitor;
use Dimsh\React\Filesystem\Monitor\MonitorConfigurator;
use TmpApp\Listener\FileEventListener;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FileSyncDaemon extends Command
{
    use LockableTrait;

    private FileEventListener $inotifyEventListener;

    private string $dirImagesSrc;

    private int $updatesListenerDirectoryLevel;

    public function __construct(
        FileEventListener $inotifyEventListener,
        string $dirImagesSrc,
        int $updatesListenerDirectoryLevel
    ) {
        parent::__construct();

        $this->inotifyEventListener = $inotifyEventListener;
        $this->dirImagesSrc = $dirImagesSrc;
        $this->updatesListenerDirectoryLevel = $updatesListenerDirectoryLevel;
    }

    protected function configure(): void
    {
        $this
            ->setName('app:file-sync:start')
            ->setDescription(
                'Стартовать демон прослушивающий файловую систему на события изменения файлов изображений.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        if (!$this->lock()) {
            $io->error('Команда уже выполняется');
            return;
        }

        (new Monitor(MonitorConfigurator::factory()
            ->setBaseDirectory($this->dirImagesSrc)
            ->setLevel($this->updatesListenerDirectoryLevel)))
            ->on(Monitor::EV_CREATE, function ($realPath) {
                $this->inotifyEventListener->onCreateOrUpdate($realPath);
            })
            ->on(Monitor::EV_MODIFY, function ($realPath) {
                $this->inotifyEventListener->onCreateOrUpdate($realPath);
            })
            ->on(Monitor::EV_DELETE, function ($realPath) {
                $this->inotifyEventListener->onDelete($realPath);
            })
            ->run();

        $this->release();
    }
}