<?php

namespace JohanCode\PatchRunner;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JohanCode\PatchRunner\Exceptions\NoPatchedFoundException;
use JohanCode\PatchRunner\Exceptions\PatchRunException;
use JohanCode\PatchRunner\Exceptions\PatchTypeError;

class PatchRunner
{
    private ?Closure $beforeCallback = null;
    private ?Closure $afterCallback = null;

    private function getPatchesDirName(): string
    {
        return config('patch-runner.patches_dir');
    }

    private function getPatchesTableName(): string
    {
        return config('patch-runner.patches_table');
    }

    private function markPatchAsApplied(string $name): void
    {
        DB::table($this->getPatchesTableName())->insert([
            'name' => $name
        ]);
    }


    public function getPatchDirectoryPath(): string
    {
        return app_path($this->getPatchesDirName());
    }

    public function getAppliedPatches(): Collection
    {
        return DB::table($this->getPatchesTableName())
            ->orderByDesc('applied_at')
            ->select('name', 'applied_at')
            ->get();
    }

    public function getAllPatches(): Collection
    {
        $result = collect([]);

        $files = File::allFiles($this->getPatchDirectoryPath());
        foreach ($files as $file) {
            $result->push((object)[
                'name' => pathinfo($file->getFilename(), PATHINFO_FILENAME),
            ]);
        }

        return $result;
    }


    public function setBeforeCallback(Closure $callback)
    {
        $this->beforeCallback = $callback;
    }

    public function setAfterCallback(Closure $callback)
    {
        $this->afterCallback = $callback;
    }

    public function runNotAppliedPatches(): int
    {
        $appliedPatches = $this->getAppliedPatches();
        $namespace = "App\\" . $this->getPatchesDirName() . "\\";

        if (!File::isDirectory($this->getPatchDirectoryPath())) {
            throw new NoPatchedFoundException();
        }

        $files = File::allFiles($this->getPatchDirectoryPath());
        if (!count($files)) {
            throw new NoPatchedFoundException();
        }

        $executedPatchCount = 0;
        foreach ($files as $file) {
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if ($appliedPatches->first(fn($p) => $p->name === $filename)) {
                continue;
            }

            $patchClassName = $namespace . $filename;
            $patchInstance = new $patchClassName;

            if ($patchInstance instanceof PatchInterface) {

                try {

                    if ($this->beforeCallback instanceof Closure) {
                        ($this->beforeCallback)($patchInstance);
                    }

                    $patchInstance->run();

                    if ($this->afterCallback instanceof Closure) {
                        ($this->afterCallback)($patchInstance);
                    }

                } catch (\Exception $e) {
                    throw new PatchRunException("Error running patch $patchClassName", 0, $e);
                }

                $this->markPatchAsApplied($filename);
                $executedPatchCount++;
            } else {
                throw new PatchTypeError("Patch $filename does not implement PatchInterface");
            }
        }

        return $executedPatchCount;
    }
}
