<?php

namespace Teguh02\FilamentDbSync\Services;

use Closure;
use Illuminate\Support\Facades\Config;

class ModelsServices 
{
    /**
     * Get the models that want to be synced
     *
     * @return array
     */
    static function getModelsWantToBeSynced() {
        $models = [];

        switch (self::modelsConfig('auto_scan')) {
         default:
         case 1:
             // Scan the models directory
             $scanned_models = self::turnModelsFilesToClassName(self::scanModelsDirectory());

             // Get the excluded models
             $exluded_models_class = (array) self::modelsConfig('excluded');

             // Filter the models
             // from the excluded models
             $models = array_filter($scanned_models, function ($model) use ($exluded_models_class) {
                 return !in_array(get_class($model), $exluded_models_class);
             });
             
             break;

         case 0:
             $models = (array) self::modelsConfig('included');
             break;
        } 

        return $models;
    }

    /**
     * Get the configuration for the models
     *
     * @return Config|Array|Bool
     */
    static function modelsConfig($key = null): Config|Array|Bool
    {
        return Config::get('db_sync.models' . ($key ? '.' . $key : ''));
    }

    /**
     * Scan the models directory
     *
     * @return array
     */
    static function scanModelsDirectory(): array
    {
        $models = [];
        $modelsDirectory = app_path('Models');
        $files = scandir($modelsDirectory);
        foreach ($files as $file) {
            if (is_file($modelsDirectory . '/' . $file)) {
                $models[] = $modelsDirectory . '/' . $file;
            }
        }
        return $models;
    }

    /**
     * Turn models files to class name
     *
     * @param [type] $files
     * @return array
     */
    static function turnModelsFilesToClassName($files): array
    {
        $models = [];
        $modelsDirectory = app_path('Models');
        foreach ($files as $file) {
            $models[] = new ("\\App\\Models\\" . str_replace([$modelsDirectory, '.php', '/'], '', $file));
        }
        return $models;
    }

    /**
     * Get the models table schema definition
     *
     * @param String $modelsName
     * @return array
     */
    static function modelsTableSchemaDefinition(String $modelsName, string $defaultColumnTypeData = 'string') : array
    {
        // Define new model instance
        $model = new $modelsName;

        // Get protected $table property
        $table = $model->getTable();

        // Get protected $fillable property
        $fillable = $model->getFillable();

        // Get protected $cast property
        $cast = $model->getCasts();

        // Map the fillable property
        $schema = array_map(function ($column) use ($cast, $defaultColumnTypeData) {
            return [
                'name' => $column,
                'type' => $cast[$column] ?? $defaultColumnTypeData,
            ];
        }, $fillable);

        // Return the schema
        return [
            'class' => $modelsName,
            'table_name' => $table,
            'schema' => $schema,
        ];
    }

    /**
     * Get the datas from the model
     *
     * @param String $model
     * @return array
     */
    static function getDatas(String $model): array
    {
        return (new $model)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->toArray();
    }
}