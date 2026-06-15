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
                'Submitted',
            ];
        })->toArray();
    }

    private function mapMode(?string $mode): string
    {
        return match ($mode) {
            'FT'         => 'Full Time',
            'PT'         => 'Part Time',
            'FT-Startup' => 'Startup Based Ph.D',
            'Integrated' => 'Integrated PG + Ph.D',
            default      => $mode ?? '',
        };
    }
}
