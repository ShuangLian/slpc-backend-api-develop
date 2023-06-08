<?php

namespace App\Exports;

use App\Exports\Sheets\UserPerSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UserExport implements WithMultipleSheets
{
    use Exportable;
    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new UserPerSheetExport(UserPerSheetExport::NEW_USER);
        $sheets[] = new UserPerSheetExport(UserPerSheetExport::MATCHED_LEGACY_USER);
        $sheets[] = new UserPerSheetExport(UserPerSheetExport::LEGACY_USER);

        return $sheets;
    }
}
