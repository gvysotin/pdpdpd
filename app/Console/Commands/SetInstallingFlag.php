<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetInstallingFlag extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:set-flag {value}'; // Принимает аргумент: true/false

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes value of IS_INSTALLING in .env file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $value = $this->argument('value');

        if (!in_array($value, ['true', 'false'])) {
            $this->error('The value must be either "true" or "false".');
            return 1;
        }

        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            $this->error('.env file not found!');
            return 1;
        }

        // Читает всё содержимое .env файла в переменную
        $envContent = File::get($envPath);

        // Заменяем значение переменной IS_INSTALLING
        $envContent = preg_replace('/^IS_INSTALLING=.*/m', "IS_INSTALLING={$value}", $envContent);

        // Записывает изменённое содержимое обратно в .env файл
        File::put($envPath, $envContent);

        $this->info("IS_INSTALLING flag successfully changed to {$value}.");
        return 0;
    }
}
