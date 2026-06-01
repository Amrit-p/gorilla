<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-crm-permissions')]
#[Description('Command description')]
class SyncCrmPermissions extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Support\CrmPermissions::syncRolesAndPermissions();

    	$this->info('Done');    

    }
}
