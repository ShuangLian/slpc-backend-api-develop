<?php

namespace App\Exports;

use App\Models\ActivityCheckin;
use App\Models\Activity;
use App\Models\UserProfile;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityCheckinExport implements FromQuery, WithMapping, WithHeadings, WithColumnWidths, WithStyles, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $index = 0;
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        // error_log($this->data);
    }
    public function query()
    {
        $request = $this->request;
        $activityCheckins = Activity::query()
            ->join("activity_checkins", "activity_checkins.activity_id", "=", "activities.id")
            ->join("user_profiles", "user_profiles.user_id", "=", "activity_checkins.user_id");
            // ->with(['userProfile', 'activity'])
            // ->orderByDesc('id');


        if ($request->has('activity_id')) {
            $activityCheckins->where('activity_checkins.activity_id', $request['activity_id']);
        }

        if ($request->has(['start_at', 'end_at'])) {
            $activityCheckins->whereBetween('activities.created_at', [$request['start_at'], $request['end_at']]);
        }
        $this->data = $activityCheckins;
        // $activityCheckins->select('activity_checkins.activity_id', 'activity_checkins.type', 'user_profiles.activity_type', 'user_profiles.user_id', 'user_profiles.name');

        return $activityCheckins;
    }

    public function map($activityCheckins): array
    {
        return [
            // $activityCheckins->activity_id,
            ++$this->index,
            // $activityCheckins->activity_type,
            // $activityCheckins->user_id,
            $activityCheckins->name
        ];
    }

    public function headings(): array
    {
        $activity = Activity::where('id', '=', $this->request['activity_id'])->firstOrFail();
        return [
            [ '題目', $activity->title ],
            [ '日期', $activity->date.' '.$activity->time ],
            [ '編號', '姓名' ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 60,           
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // $sheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        return [
            'A' => [
                'font' => ['size' => 14],
                'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                ]],
            'B' => [
                'font' => ['size' => 14],
                'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        'wrapText' => true
                ]],
            1 => [ 'font' => [ 'size' => 16, 'bold' => true ] ],
            2 => [ 'font' => [ 'size' => 16, 'bold' => true ] ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDefaultRowDimension()->setRowHeight(40);  // All rows
                // $event->sheet->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // $event->sheet->styleCells(
                //     'A1:W1',
                //     [
                //         'borders' => [
                //             'outline' => [
                //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                //                 'color' => ['argb' => 'FFFF0000'],
                //             ],
                //         ]
                //     ]
                // );
            },
        ];
    }
}
