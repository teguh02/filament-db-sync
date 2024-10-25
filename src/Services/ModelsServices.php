<?php

namespace Teguh02\FilamentDbSync\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Teguh02\FilamentDbSync\Models\DbSync;

class ModelsServices
{
    /**
     * Get the models that want to be synced
     *
     * @return array
     */
    public static function getModelsWantToBeSynced()
    {
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
                    return ! in_array(get_class($model), $exluded_models_class);
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
     */
    public static function modelsConfig($key = null): Config | array | bool
    {
        return Config::get('db_sync.models' . ($key ? '.' . $key : ''));
    }

    /**
     * Scan the models directory
     */
    public static function scanModelsDirectory(): array
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
     */
    public static function turnModelsFilesToClassName($files): array
    {
        $models = [];
        $modelsDirectory = app_path('Models');
        foreach ($files as $file) {
            $models[] = new ('\\App\\Models\\' . str_replace([$modelsDirectory, '.php', '/'], '', $file));
        }

        return $models;
    }

    /**
     * Get the models table schema definition
     */
    public static function modelsTableSchemaDefinition(string $modelsName, string $defaultColumnTypeData = 'string'): array
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
     */
    public static function getDatas(string $model): array
    {
        return (new $model)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get the table datas
     */
    public static function getTableDatas(string $table_name): array
    {
        return DB::table($table_name)
            ->get()
            ->toArray();
    }

    /**
     * Get the table primary key from the config
     */
    public static function getTablePrimaryKeyFromConfig(string $table_name): string
    {
        $primary_key = 'id';
        $tables_keys = [];

        foreach (self::modelsConfig('column_as_key') as $key => $value) {
            if (class_exists($key)) {
                $tables_keys[(new $key)->getTable()] = $value;
            } else {
                $tables_keys[$key] = $value;
            }
        }

        return isset($tables_keys[$table_name]) ? $tables_keys[$table_name] : $primary_key;
    }

    /**
     * Create the table schema
     */
    public static function createTableSchema(array $model_definition, string $plugin_ids): bool
    {
        // Definition : {"class":"App\\Models\\Items","table_name":"items","schema":[{"name":"name","type":"string"},{"name":"description","type":"string"},{"name":"price","type":"integer"},{"name":"stock","type":"integer"},{"name":"expired_at","type":"date"}]}

        // Check if the table exists
        if (! Schema::hasTable($model_definition['table_name'])) {
            try {
                Schema::create($model_definition['table_name'], function ($table) use ($model_definition) {
                    // Create the primary key
                    $table->id();

                    // Create the table schema
                    foreach ($model_definition['schema'] as $column) {
                        // Handle hashed type
                        if ($column['type'] == 'hashed') {
                            $table->string($column['name'])->nullable();

                            // Handle the other types
                        } else {
                            $table->{$column['type']}($column['name'])->nullable();
                        }
                    }

                    // Create the timestamps
                    $table->timestamps();
                    $table->softDeletes();
                });
            } catch (\Throwable $th) {
                Log::error('[' . $plugin_ids . '] Error creating table: ' . $model_definition['table_name']);
                Log::error('[' . $plugin_ids . '] Error message: ' . $th->getMessage());

                return false;
            }

            Log::info('[' . $plugin_ids . '] Table created: ' . $model_definition['table_name']);
        }

        Log::info('[' . $plugin_ids . '] Table already exists: ' . $model_definition['table_name']);

        return true;
    }

    /**
     * Store the data to the database
     */
    public static function storeDataToDatabase(string $model_primary_key, array $model_definition, array $model_datas, string $plugin_ids, array $sync_config): void
    {
        // Definition : {"class":"App\\Models\\Items","table_name":"items","schema":[{"name":"name","type":"string"},{"name":"description","type":"string"},{"name":"price","type":"integer"},{"name":"stock","type":"integer"},{"name":"expired_at","type":"date"}]}
        // Datas : [{"id":9,"name":"Dr. Barney Simonis DVM","email":"krippin@hotmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":10,"name":"Lizzie Aufderhar","email":"hirthe.stanley@hill.info","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":11,"name":"Anastasia Davis","email":"bradley.doyle@schiller.net","email_verified_at":null,"created_at":"2024-08-30T17:49:09.000000Z","updated_at":"2024-08-30T17:49:09.000000Z"},{"id":6,"name":"Hyman Graham","email":"weldon02@yahoo.com","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":7,"name":"Thurman Douglas","email":"hallie.cremin@mayer.biz","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":8,"name":"Mrs. Margaret Lang","email":"watsica.cassandre@ortiz.com","email_verified_at":null,"created_at":"2024-08-30T17:49:08.000000Z","updated_at":"2024-08-30T17:49:08.000000Z"},{"id":2,"name":"Novella Hudson","email":"uhackett@emard.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":3,"name":"Karley Schmitt","email":"buster59@gmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":4,"name":"Christop Johnston II","email":"johann02@mante.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":5,"name":"Delores O'Hara","email":"vbernier@gmail.com","email_verified_at":null,"created_at":"2024-08-30T17:49:07.000000Z","updated_at":"2024-08-30T17:49:07.000000Z"},{"id":1,"name":"Admin","email":"admin@gmail.com","email_verified_at":null,"created_at":"2024-08-30T14:11:52.000000Z","updated_at":"2024-08-30T14:11:52.000000Z"}]

        // Get the duplicate data action
        $duplicate_data_action = $sync_config['duplicate_data_action'];

        // Get tabel schema
        $schema = $model_definition['schema'];

        try {
            switch ($duplicate_data_action) {

                // Handle the update data action
                default:
                case 'update':
                    foreach ($model_datas as $model_data) {
                        $data = DB::table($model_definition['table_name'])
                            ->where($model_primary_key, $model_data[$model_primary_key])
                            ->first();

                        if ($data) {
                            $data = [];

                            foreach ($schema as $column) {
                                if (in_array($column['name'], ['created_at', 'updated_at'])) {
                                    $data[$column['name']] = Carbon::parse($model_data[$column['name']]) ->format('Y-m-d H:i:s') ?? now();
                                } else {
                                    $data[$column['name']] = $model_data[$column['name']] ?? null;
                                }
                            }

                            // Check if the data not have timestamp
                            if (! isset($data['created_at']) or blank($data['created_at'])) {$data['created_at'] = now() -> format('Y-m-d H:i:s');}
                            if (! isset($data['updated_at']) or blank($data['updated_at'])) {$data['updated_at'] = now() -> format('Y-m-d H:i:s');}

                            Log::info('[' . $plugin_ids . '] Data to be updated: ' . json_encode($data));

                            DB::table($model_definition['table_name'])
                                ->where($model_primary_key, $model_data[$model_primary_key])
                                ->update($data);
                        } else {
                            $data = [];

                            // Create the database instance
                            $db = DB::table($model_definition['table_name']);

                            // manual increment id
                            $data['id'] = $db->orderBy('id', 'desc')->first()?->id + 1 ?? 1;

                            foreach ($schema as $column) {
                                if (in_array($column['name'], ['created_at', 'updated_at'])) {
                                    $data[$column['name']] = Carbon::parse($model_data[$column['name']]) ->format('Y-m-d H:i:s') ?? now();
                                } else {
                                    $data[$column['name']] = $model_data[$column['name']] ?? null;
                                }
                            }

                            // Check if the data not have timestamp
                            if (! isset($data['created_at']) or blank($data['created_at'])) {$data['created_at'] = now() -> format('Y-m-d H:i:s');}
                            if (! isset($data['updated_at']) or blank($data['updated_at'])) {$data['updated_at'] = now() -> format('Y-m-d H:i:s');}

                            Log::info('[' . $plugin_ids . '] Data to be inserted: ' . json_encode($data));

                            $db->insert($data);
                        }
                    }

                    break;

                    // Handle the duplicate data action
                case 'duplicate':
                    foreach ($model_datas as $model_data) {
                        $data = [];

                        // Create the database instance
                        $db = DB::table($model_definition['table_name']);

                        // manual increment id
                        $data['id'] = $db->orderBy('id', 'desc')->first()?->id + 1 ?? 1;

                        foreach ($schema as $column) {
                            if (in_array($column['name'], ['created_at', 'updated_at'])) {
                                $data[$column['name']] = Carbon::parse($model_data[$column['name']]) ->format('Y-m-d H:i:s') ?? now();
                            } else {
                                $data[$column['name']] = $model_data[$column['name']] ?? null;
                            }
                        }

                        // Check if the data not have timestamp
                        if (! isset($data['created_at']) or blank($data['created_at'])) {$data['created_at'] = now() -> format('Y-m-d H:i:s');}
                        if (! isset($data['updated_at']) or blank($data['updated_at'])) {$data['updated_at'] = now() -> format('Y-m-d H:i:s');}

                        // Insert the data
                        Log::info('[' . $plugin_ids . '] Data to be inserted: ' . json_encode($data));

                        // Insert the data
                        $db->insert($data);
                    }

                    break;
            }
        } catch (\Throwable $th) {
            // Store the failed job to the database
            DbSync::create([
                'model' => $model_definition['class'],
                'model_id' => null,
                'action' => 'pull',
                'data' => json_encode($model_datas),
                'status' => 'failed',
                'failed_at' => now(),
                'failed_reason' => $th->getMessage() . ' ' . $th->getTraceAsString(),
            ]);

            // Log the error
            Log::error('[' . $plugin_ids . '] ' . $th->getMessage() . ' ' . $th->getTraceAsString());
        }
    }
}
