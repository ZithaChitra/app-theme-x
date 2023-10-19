<?php

namespace MSBTheme\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class InstallMsbTheme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msbtheme:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install MSB Theme App-Theme-X';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $themeName = 'appthemex';
        $this->info("MSB App-Theme-X Installing");
        try {

            $folderPath = 'themes/' . $themeName;            
            if(Storage::disk('public_uploads')->exists($folderPath)){
                $deleted = Storage::disk('public_uploads')->deleteDirectory($folderPath);
            }
            $this->call('vendor:publish', [
                '--provider' => "MSBTheme\MSBThemeServiceProvider",
                '--tag'      => 'assets'
            ]);

        } catch (\Throwable $th) {
            $this->error('An error occurred. Could not publish App-Theme-X assets.');
        }


        // update installed themes
        $appThemesP = '/.well-known/themes.json';
        $fileData = [];
        if(Storage::disk('public_uploads')->exists($appThemesP)){
            $this->info("Updating themes.json");
            $prev = Storage::disk('public_uploads')->get($appThemesP);
            $prev = json_decode($prev);
            $instThemes = [];
            
            if(property_exists($prev, 'installed_themes')){
                $instThemes = $prev->installed_themes;
                if(!in_array($themeName, $instThemes)){
                    $instThemes[] = $themeName;    
                }
            }

            $fileData = json_encode(['installed_themes' => $instThemes]);
        }else{
            $fileData = json_encode(['installed_themes' => ['default', $themeName]]);
        }

        $saved = Storage::disk('public_uploads')->put($appThemesP, $fileData);
        if($saved){
            $this->info('Successfully updated themes.json');
        }else{
            $this->error('An error occurred. Could not update themes.json');
        }



        if(class_exists('BasePack\Models\SettingTheme')){
            try {
                \BasePack\Models\SettingTheme::firstOrCreate(
                    ['name' => $themeName],
                    [
                        'name'     => $themeName,
                        'active'   => false,
                        'created'  => now(),
                        'modified' => now(),
                    ]
                );          
            } catch (\Throwable $th) {
                $this->error('An error occurred. Could not add App-Theme-X to database');
            }
        }else{
            $this->error('An error occurred. Could not find eloquent model: "App\Models\SettingTheme"');
        }




        $this->info('Done !');
        
        return 0;
    }
}
