<?php

namespace App\Command;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class WeatherApiCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('sync:weather')
            ->addOption('latitude', null, InputOption::VALUE_REQUIRED, 'Option latitude')
            ->addOption('longitude', null, InputOption::VALUE_REQUIRED, 'Option longitude')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Weather API');

        // Проверка полученных значений
        $latitude = $input->getOption('latitude');
        $longitude = $input->getOption('longitude');

        if (!$latitude || !$longitude) {
            $io->error('Coordinates required! (latitude and longitude)');
            return Command::FAILURE;
        }

        $client = new Client();
        try {
            $res = $client->get('https://api.open-meteo.com/v1/forecast', [
                'query' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'hourly' => 'temperature_2m',
                ]
            ])->getBody()->getContents();

            $resultData = json_decode($res, true);

            $io->block(sprintf(
                '%s in %s, %s',
                $resultData['hourly_units']['temperature_2m'],
                $input->getOption('latitude'),
                $input->getOption('longitude')
            ), 'Temperature', 'fg=black;bg=green', ' ', true);

            $table = new Table($output);
            $table->setHeaders([
                '<fg=black>Date</>', '<fg=black>Temperature</>'
            ]);

            $data = [];
            foreach ($resultData['hourly']['time'] as $index => $date) {
                $data[] = [Carbon::parse($date)->format('Y.m.d H:i:s'), $resultData['hourly']['temperature_2m'][$index]];
            }

            $table->setRows($data);
            $table->render();
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
