<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithStartRow;

class DedicationImport implements WithStartRow
{
    public function startRow(): int
    {
        return 6;
    }
}
