<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MergeUserRegistrationUnitTestFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge-user-registration-unit-tests-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges the contents of user registation unit tests into one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Используем абсолютные пути относительно корня проекта
        $files = [
            base_path('tests/Unit/Application/Registration/Handlers/RegisterUserCommandHandlerTest.php'),
            base_path('tests/Unit/Domain/Registration/DTO/UserRegistrationDataTest.php'),
            base_path('tests/Unit/Domain/Registration/Factories/UserFactoryTest.php'),
            base_path('tests/Unit/Domain/Registration/Services/UserCreatorTest.php'),
            base_path('tests/Unit/Domain/Registration/ValueObjects/EmailTest.php'),
            base_path('tests/Unit/Domain/Registration/ValueObjects/HashedPasswordTest.php'),
            base_path('tests/Unit/Domain/Shared/Enums/OperationResultEnumTest.php'),
            base_path('tests/Unit/Domain/Shared/Results/OperationResultTest.php'),
            base_path('tests/Unit/Jobs/Registration/SendWelcomeEmailJobTest.php'),
            base_path('tests/Unit/Registration/Registration/SendWelcomeEmailListenerTest.php'),
        ];
        
        // Путь к выходному файлу (лучше сохранять в storage)
        $outputFile = storage_path('app/merged_user_registration_unit_test_files.txt');

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
