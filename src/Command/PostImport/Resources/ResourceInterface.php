<?php

namespace App\Command\PostImport\Resources;

use App\Command\PostImport\Data\PostCollection;

interface ResourceInterface
{
    public function execute(): PostCollection;
}