<?php

namespace App\Exports;

use App\Exports\Sheets\VisitPerSheetExport;
use App\Models\Visit;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VisitExport implements WithMultipleSheets
{
    use Exportable;

    private $from;
    private $to;

    public function __construct(string $from, string $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new VisitPerSheetExport(Visit::STATUS_DONE, $this->from, $this->to);
        $sheets[] = new VisitPerSheetExport(Visit::STATUS_PENDING, $this->from, $this->to);
        $sheets[] = new VisitPerSheetExport(Visit::STATUS_PROCESSING, $this->from, $this->to);

        return $sheets;
    }
}
