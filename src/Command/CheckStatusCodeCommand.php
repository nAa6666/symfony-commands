<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckStatusCodeCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:status-code')
            ->setDescription('Check status code of a website')
            ->addArgument('url', InputArgument::REQUIRED, 'URL of the website');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $url = $input->getArgument('url');

        $io->title('Checking site status');

        $client = new Client(['allow_redirects' => false]);
        $response = $client->get($url);
        $httpCode = $response->getStatusCode();

        if ($httpCode === 200) {
            $io->success('The site is available (HTTP code 200)');
        } else {
            $io->error("The site is unavailable, HTTP code: $httpCode");
        }

        return Command::SUCCESS;
    }
}
