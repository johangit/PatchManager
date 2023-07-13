<?php

namespace JohanCode\PatchRunner\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use JohanCode\PatchRunner\Exceptions\NoPatchedFoundException;
use JohanCode\PatchRunner\Exceptions\PatchRunException;
use JohanCode\PatchRunner\Exceptions\PatchRunnerException;
use JohanCode\PatchRunner\Exceptions\PatchTypeError;
use JohanCode\PatchRunner\PatchInterface;
use JohanCode\PatchRunner\PatchRunner;

class PatchCommand extends Command
{
    protected $signature = 'patch';
    protected $description = 'Runs all patches which did not run yet';

    private PatchRunner $patchRunner;

    public function __construct(PatchRunner $patchRunner)
    {
        parent::__construct();
        $this->patchRunner = $patchRunner;
    }

    public function handle()
    {
        $this->patchRunner->setBeforeCallback(function (PatchInterface $patch) {
            $this->line('Patch [' . $patch::class . ']: running');
        });
        $this->patchRunner->setAfterCallback(function (PatchInterface $patch) {
            $this->line('Patch [' . $patch::class . ']: success');
        });

        try {

            $executedPatchCount = $this->patchRunner->runNotAppliedPatches();

            if ($executedPatchCount) {
                $this->info("DONE. Patches executed: $executedPatchCount");
            } else {
                $this->info('No pending patches to run');
            }

        } catch (NoPatchedFoundException $e) {
            $this->info('No patches found in directory: ' . $this->patchRunner->getPatchDirectoryPath());
        } catch (PatchRunnerException $e) {
            Log::error($e);
            $this->error("Patch execution error");
        }
    }
}
