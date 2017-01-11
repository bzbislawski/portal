<?php
namespace Portal;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunFormatterCommand extends Command
{
    private $arrayFormatter;

    public function __construct(ArrayFormatter $arrayFormatter)
    {
        $this->arrayFormatter = $arrayFormatter;

        parent::__construct();
    }

    /**
     *  Configure command.
     */
    public function configure()
    {
        $this->setName('run')
            ->setDescription('Format data in file')
            ->addArgument('name', InputArgument::REQUIRED, 'File name')
            ->setHelp("To format a table just type without commas: ./portal run 'file_name'.");
    }

    /**
     * Execute csv file portal command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $fileName = $input->getArgument('name');
        if( ! file_exists($fileName)) {
            throw new \Exception('File does not exists!');
        }

        $this->arrayFormatter->readFile($fileName);
        $this->arrayFormatter->deleteHeader();
        $this->arrayFormatter->reindexArray();
        $this->arrayFormatter->sumDuplicatedEntries();
        $this->arrayFormatter->reindexArray();
        $this->arrayFormatter->explodeLocationDetails();
        $this->arrayFormatter->sortArray();
        $this->arrayFormatter->removeLocationDetails();
        $this->arrayFormatter->dumpOutput();
        $this->arrayFormatter->showArray($output);

        $output->writeln("<info>Output.csv has been successfully saved.<info>");
    }
}