<?php

namespace App\Console\Commands;

use App\Models\BusinessSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LocalDatabaseRefresh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'local:database-refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh local database settings for development';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->upsertBusinessSetting('timezone', 'Asia/Dhaka');
        $this->upsertBusinessSetting('country_code', 'BD');

        $recaptcha = getWebConfig(name: 'recaptcha');
        $recaptchaValue = json_encode(['status' => 0, 'site_key' => $recaptcha['site_key'], 'secret_key' => $recaptcha['secret_key']]);
        $this->upsertBusinessSetting('recaptcha', $recaptchaValue);

        Artisan::call('optimize:clear');
        $this->info('Local database settings refreshed successfully.');
    }

    private function upsertBusinessSetting(string $type, string $value): void
    {
        BusinessSetting::updateOrCreate(['type' => $type], ['value' => $value]);
    }
}
