<?php

namespace Zschuessler\ModelJsonAttributeGuard\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class MakeModelJsonAttributeGuardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:model-json-attribute-guard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a JsonAttributeGuard class for a chosen model';

    /**
     * Models
     *
     * A list of autoloaded Models. Useful for picking a Model for an autocomplete.
     *
     * @var Collection
     */
    protected $models;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Set models
        $this->models = $this->getModelClasses();
        if (!$this->models->count()) {
            $this->error('Unable to find Models. Please update your configuration to set the model_path and model_namespace values');
            return;
        }
    }

    /**
     * Execute the console command.
     *
     * @param  \App\DripEmailer  $drip
     * @return mixed
     */
    public function handle()
    {
        // Step 1: Get Model Class
        $modelChoice = $this->anticipate(
            "Which Model? \n\r Start typing the namespace to autocomplete.",
            $this->models->pluck('classname')->toArray()
        );

        // Step 2: Get Model table
        /** @var $model Model */
        $model = new $modelChoice;
        $table = $model->getTable();

        // Step 3: Select table column
        $columns = collect(Schema::getColumnListing($table));
        $columnChoice = $this->choice(
            'Select the table column',
            $columns->toArray()
        );

        /**
         * Step 4: Create the class
         *
         * Path is:
         * 1. Config namespace for models (eg App\Models)
         * 2. CamelCase folder of the class name picked (eg App\Models\Books)
         * 3. New file: {$column}JsonAttributeGuard (eg App\Models\Books\AuthorsJsonAttributeGuard.php)
         */
        $isConfirmed = $this->confirm(sprintf(
            'Confirm new file will be created: %s\%s\%sJsonAttributeGuard.php',
            config('model-json-attributes-guard.model_namespace'),
            Str::title(Str::singular($table)),
            Str::title($columnChoice)
        ), true);

        if (true === $isConfirmed) {
            $this->createGuardFile(
                $model, $columnChoice
            );
        }
    }

    /**
     * Create Guard File
     *
     * Given the Model and column name, create a JsonAttributeGuard.
     *
     * @param Model $model
     * @param $columnName string
     */
    public function createGuardFile(Model $model, $columnName)
    {
        /**
         * Step 1: Create file contents from a stub file
         */
        $stubContents = file_get_contents(
            __DIR__ . '/stubs/ModelJsonAttributeGuard.stub'
        );
        $stubReplacements = collect([
            '$CLASS_NAMESPACE$' => sprintf(
                '%s\%s',
                config('model-json-attributes-guard.model_namespace'),
                Str::title(Str::singular($model->getTable()))
            ),
            '$COLUMN_BASENAME$' => Str::title($columnName),
            '$EXTENDED_CLASS$'  => 'Zschuessler\ModelJsonAttributeGuard\JsonAttributeGuard',
            '$EXTENDED_CLASS_BASENAME$'  => 'JsonAttributeGuard',
        ]);
        $stubReplacements->each(function($replaceValue, $replaceKey) use (&$stubContents) {
            $stubContents = str_replace($replaceKey, $replaceValue, $stubContents);
        });

        /**
         * Step 2: save the file
         */
        $basePath     = base_path(config('model-json-attributes-guard.model_path'));
        $folderPath   = sprintf('%s/%s', $basePath, Str::title(Str::singular($model->getTable())));
        $fullFilePath = sprintf('%s/%sJsonAttributeGuard.php', $folderPath, Str::title($columnName));

        // Create folder if it doesn't exist
        if (!File::exists($folderPath)) {
            File::makeDirectory($folderPath);
        }

        // Create file
        file_put_contents(
            $fullFilePath,
            $stubContents
        );

        // All done, have fun
        $this->info('File created: ' . $fullFilePath);
    }

    /**
     * Get Model Classes
     *
     * Autoloads all Model classes according to the namespace/path found in the configuration file.
     * This is only used to supply data to an autocomplete.
     *
     * @return Collection
     */
    public function getModelClasses()
    {
        $modelPath = config('model-json-attributes-guard.model_path');
        $modelClasses = collect(\File::allFiles(
            base_path($modelPath)
        ));

        $modelClasses->transform(function(SplFileInfo $modelClass) {
            $modelClass->classname = str_replace(
                [app_path(), '/', '.php'],
                ['App', '\\', ''],
                $modelClass->getRealPath()
            );

            $modelClass->shortname = $modelClass->getBasename('.php');

            return $modelClass;
        });

        return $modelClasses;
    }
}
