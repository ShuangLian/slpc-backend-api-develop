<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithStartRow;

class PermissionConstraintImport implements WithStartRow
{
    public function startRow(): int
    {
        return 5;
    }
}
