<?php

namespace JohanCode\PatchRunner\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakePatchCommand extends Command
{
    protected $signature = 'make:patch {name : The name of the patch}';
    protected $description = 'Generate a new patch file';

    public function handle()
    {
        $patchesDir = config('patch-runner.patches_dir');

        $path = app_path($patchesDir);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }

        $className = Str::studly($this->argument('name'));
        $fileName = $path . DIRECTORY_SEPARATOR . "{$className}.php";
        if (File::exists($fileName)) {
            $this->error("Patch '{$className}' already exists.");
            return;
        }

        $template = file_get_contents(__DIR__ . '/../../templates/Patch.template');
        $template = str_replace('{{_namespace_}}', $patchesDir, $template);
        $template = str_replace('{{_classname_}}', $className, $template);

        file_put_contents($fileName, $template);

        $this->info("Patch '{$className}' generated successfully.");
    }
}
