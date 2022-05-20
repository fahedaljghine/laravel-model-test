<?php

namespace Fahedaljghine\ModelTest\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class GenerateTests extends Command
{
    protected $signature = 'test:generate';

    protected $description = 'Generate tests for models how has HasTests trait';

    protected $files;
    protected $stubVariables;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->files = $filesystem;
    }

    public function handle()
    {
        $models = config('model-test.models');
        foreach ($models as $model) {
            $this->stubVariables = [
                'MODEL_NAME' => $model,
                'LOWER_NAME' => strtolower($model),
                'PlURAL_NAME' => Str::plural(strtolower($model))
            ];

            $path = base_path("tests/Feature/" . Str::plural($model) . "Test.php");

            $stub_path = base_path('stubs/laravel-model-test/test.stub');

            $this->saveFile($stub_path, $path);
        }
    }


    protected function saveFile($stub_path, $path)
    {
        if (!file_exists(dirname($path))) {
            $this->files->makeDirectory(dirname($path));
        }

        $contents = file_get_contents($stub_path);

        foreach ($this->stubVariables as $search => $replace) {
            $contents = str_replace('$' . $search . '$', $replace, $contents);
        }

        if (!$this->files->exists($path)) {
            $this->files->put($path, $contents);
            $this->info("File : {$path} created");
        } else {
            $this->info("File : {$path} already exits");
        }
    }
}
