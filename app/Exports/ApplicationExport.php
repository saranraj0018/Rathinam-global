<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplicationExport implements FromArray, WithHeadings
{
    public function __construct(protected Collection $applications) {}

    public function headings(): array
    {
        return [
            'Application Number',
            'Name',
            'Email',
            'Phone',
            'Specialization',
            'Programme Mode',
            'Created At',
            'Submitted At',
            'Status'
        ];
    }

    public function array(): array
    {
        return $this->applications->map(function ($a) {
            return [
                $a->application_no,
                $a->full_name,
                $a->email,
                $a->mobile,
                $a->specialization,
                $this->mapMode($a->programme_mode),
                $a->submitted_at,
                $a->created_at,
                $a->status,
            ];
        })->toArray();
    }

    private function mapMode(?string $mode): string
    {
        return match ($mode) {
            'FT'         => 'Full Time',
            'PT'         => 'Part Time',
            'full_time'  => 'Full Time',
            'part_time'  => 'Part Time',
            'FT-Startup' => 'Startup Based Ph.D',
            'Integrated' => 'Integrated PG + Ph.D',
            default      => $mode ?? '',
        };
    }
}
