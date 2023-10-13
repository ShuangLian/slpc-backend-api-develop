<?php

namespace App\Exports\Sheets;

use App\Models\Visit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class VisitPerSheetExport implements FromArray, WithTitle, WithHeadings
{
    private $status;
    private $from;
    private $to;

    public function __construct(string $status, string $from, string $to)
    {
        $this->status = $status;
        $this->from = $from;
        $this->to = $to;
    }

    public function array(): array
    {
        $visits = Visit::query()
            ->where('status', $this->status)
            ->whereMonth('visit_date', '>=', Carbon::parse($this->from)->month)
            ->whereMonth('visit_date', '<=', Carbon::parse($this->to)->month)
            ->with(['userProfile', 'zone', 'visitReason'])
            ->get();

        $array = [];
        foreach ($visits as $visit) {
            $array[] = [
                'name' => $visit->getRelation('userProfile')['name'] ?? null,
                'zone' => $visit->getRelation('zone')['zone']['name'] ?? null,
                'reason' => $visit->getRelation('visitReason')['reason'] ?? null,
                'visit_date' => $visit['visit_date'] ?? null,
            ];
        }

        return $array;
    }

    public function title(): string
    {
        $title = '';

        if ($this->status == Visit::STATUS_PENDING) {
            $title = '未訪問';
        }

        if ($this->status == Visit::STATUS_PROCESSING) {
            $title = '訪問中';
        }

        if ($this->status == Visit::STATUS_DONE) {
            $title = '已結束';
        }

        return $title;
    }

    public function headings(): array
    {
        return ['姓名', '牧區', '事由', '預計探訪時間'];
    }
}
