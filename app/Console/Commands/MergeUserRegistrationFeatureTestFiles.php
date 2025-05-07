<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MergeUserRegistrationFeatureTestFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge-user-registration-feature-tests-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges the contents of user registation feature tests into one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Используем абсолютные пути относительно корня проекта
        $files = [
            base_path('tests/Feature/Auth/ConcurrentRegistrationTest.php'),
            base_path('tests/Feature/Auth/RegisterDatabaseTest.php'),
            base_path('tests/Feature/Auth/RegisterFailureTest.php'),
            base_path('tests/Feature/Auth/RegisterJobQueueTest.php'),
            base_path('tests/Feature/Auth/RegisterSecurityTest.php'),
            base_path('tests/Feature/Auth/RegisterSuccessTest.php'),
            base_path('tests/Feature/Auth/RegisterValidationTest.php'),
            base_path('tests/Feature/Auth/RegistrationRouteTest.php'),
            base_path('tests/Feature/Domain/Registration/RegisterUserTest.php'),
            base_path('tests/Feature/Domain/Registration/RegistrationFlowTest.php'),
            base_path('tests/Feature/Http/Requests/RegisterUserRequestTest.php'),
        ];
        
        // Путь к выходному файлу (лучше сохранять в storage)
        $outputFile = storage_path('app/merged_user_registration_feature_test_files.txt');

        $content = '';

        foreach ($files as $file) {
            if (file_exists($file)) {
                $content .= "// File: " . basename($file) . "\n\n";
                $content .= file_get_contents($file) . "\n\n";
            } else {
                $this->error("File $file not found.");
                return;
            }
        }

        // Записываем объединенный контент в выходной файл
        file_put_contents($outputFile, $content);
        $this->info("Files have been successfully merged into $outputFile");
    }
}
