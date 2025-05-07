<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MergeUserRegistrationFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'merge-user-registration-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges the contents of user registration files into one';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Используем абсолютные пути относительно корня проекта
        $files = [
            base_path('app/Application/Registration/Actions/RegisterUserAction.php'),
            base_path('app/Application/Registration/Contracts/RegisterUserActionInterface.php'),
            base_path('app/Domain/Registration/Contracts/EmailNotificationServiceInterface.php'),
            base_path('app/Domain/Registration/Contracts/EmailSpecificationInterface.php'),
            base_path('app/Domain/Registration/Contracts/UserCreatorInterface.php'),
            base_path('app/Domain/Registration/Contracts/UserFactoryInterface.php'),
            base_path('app/Domain/Registration/DTO/UserRegistrationData.php'),
            base_path('app/Domain/Registration/Exceptions/UserRegistrationException.php'),
            base_path('app/Domain/Registration/Factories/UserFactory.php'),
            base_path('app/Domain/Registration/Services/EmailNotificationService.php'),
            base_path('app/Domain/Registration/Services/UserCreator.php'),
            base_path('app/Domain/Registration/Specifications/UniqueEmailSpecification.php'),
            base_path('app/Domain/Registration/ValueObjects/Email.php'),
            base_path('app/Domain/Registration/ValueObjects/HashedPassword.php'),
            base_path('app/Domain/Registration/ValueObjects/PlainPassword.php'),
            base_path('app/Domain/Shared/Enums/OperationResultEnum.php'),
            base_path('app/Domain/Shared/Results/OperationResult.php'),       
            base_path('app/Events/Registration/UserRegistered.php'),
            base_path('app/Http/Requests/CreateUserRequest.php'),
            base_path('app/Jobs/Registration/SendWelcomeEmailJob.php'),
            base_path('app/Listeners/Registration/SendWelcomeEmailListener.php'),
            base_path('app/Mail/Registration/WelcomeEmail.php'),
            base_path('app/Rules/NoHtml.php'),

        ];
        
        // Путь к выходному файлу (лучше сохранять в storage)
        $outputFile = storage_path('app/merged_user_registration_files.txt');

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
