<?php

namespace JohanCode\PatchRunner\Console\Commands;

use Illuminate\Console\Command;
use JohanCode\PatchRunner\PatchRunner;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class PatchStatusCommand extends Command
{
    protected $signature = 'patch:status';
    protected $description = 'Shows all patches and checks status';

    private PatchRunner $patchRunner;

    public function __construct(PatchRunner $patchRunner)
    {
        parent::__construct();
        $this->patchRunner = $patchRunner;
    }

    public function handle()
    {
        $allPatches = $this->patchRunner->getAllPatches();
        $appliedPatches = $this->patchRunner->getAppliedPatches();

        $result = $allPatches->transform(function ($patch) use ($appliedPatches) {
            $appliedInfo = $appliedPatches->first(fn($el) => $el->name === $patch->name);

            return [
                'name' => $patch->name,
                'executed' => $appliedInfo ? 'Yes' : 'No',
                'date' => $appliedInfo ? $appliedInfo->applied_at : null,
            ];
        });

        if ($result->isEmpty()) {
            $this->info('No patches found in directory: ' . $this->patchRunner->getPatchDirectoryPath());
            return;
        }

        $output = new ConsoleOutput();
        $table = new Table($output);

        $table->setHeaders(['Name', 'Executed', 'Date']);

        $result->each(function ($el) use ($table, $output) {
            $table->addRow([$el['name'], $el['executed'], $el['date']]);
        });

        $table->render();
    }
}
