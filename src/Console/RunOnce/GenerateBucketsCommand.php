<?php
declare(strict_types=1);

namespace TmpApp\Console\RunOnce;

use TmpApp\Service\FileService;
use TmpApp\Service\ZipService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateBucketsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:buckets:generate';

    private FileService $updatedFileService;

    private ZipService $zipService;

    public function __construct(FileService $updatedFileService, ZipService $zipService)
    {
        parent::__construct();

        $this->updatedFileService = $updatedFileService;
        $this->zipService = $zipService;
    }

    protected function configure(): void
    {
        $this->setDescription('Создание zip-бакетов из директорий.');
    }

    /**
     * @throws \Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());

        if (!$this->lock()) {
            $io->error('Команда уже выполняется');
            return;
        }

        foreach ($this->updatedFileService->getAllDirectories() as $dir) {
            $this->zipService->addFilesFromDir($dir);
        }

        $this->release();
    }
}
