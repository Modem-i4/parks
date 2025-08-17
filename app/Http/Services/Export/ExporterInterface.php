<?php

namespace App\Http\Services\Export;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface ExporterInterface
{
    public function export(Collection $markers): Response;
}
