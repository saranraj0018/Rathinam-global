<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ApplicationExport implements FromArray, WithHeadings
{
    public function __construct(
        protected Collection $applications
    ) {}

    public function headings(): array
    {
        return [
            'Application Number',
            'Name',
            'Email',
            'Phone',
            'Religion',
            'Nationality',
            'Specialization',
            'Programme Mode',
            'Languages',
            'Education',
            'Services',
            'Projects',
            'Courses',
            'Career Aspirations',
            'Submitted At',
            'Created At',
            'Status',
        ];
    }

    public function array(): array
    {
        return $this->applications->map(function ($a) {

            $languages = $a->languages
                ->map(function ($language) {

                    $skills = [];

                    if ($language->can_read) {
                        $skills[] = 'Read';
                    }

                    if ($language->can_write) {
                        $skills[] = 'Write';
                    }

                    if ($language->can_speak) {
                        $skills[] = 'Speak';
                    }

                    return $language->language . ' (' . implode('/', $skills) . ')';
                })
                ->implode(' | ');

            $educations = $a->educations
                ->map(function ($education) {
                    return sprintf(
                        '%s | %s | %s | %s | %s',
                        $education->education_level,
                        $education->subjects,
                        $education->institution,
                        $education->passing_date,
                        $education->marks
                    );
                })
                ->implode(' || ');

            $services = $a->services
                ->map(function ($service) {
                    return sprintf(
                        '%s | %s | %s | %s | %s',
                        $service->designation,
                        $service->institution,
                        $service->from_date,
                        $service->to_date,
                        $service->total_duration
                    );
                })
                ->implode(' || ');

            $projects = $a->projects
                ->map(function ($project) {
                    return $project->title . ' (' . $project->status . ')';
                })
                ->implode(' | ');

            $courses = $a->courses
                ->map(function ($course) {
                    return $course->course_name .
                        ' (' . ($course->completed ? 'Completed' : 'Not Completed') . ')';
                })
                ->implode(' | ');

            $aspirations = $a->aspirations
                ->pluck('aspiration')
                ->implode(' | ');

            return [
                $a->application_no,
                $a->full_name,
                $a->email,
                $a->mobile,
                $a->religion,
                $a->nationality,
                $a->specialization,
                $this->mapMode($a->programme_mode),
                $languages,
                $educations,
                $services,
                $projects,
                $courses,
                $aspirations,
                $a->submitted_at,
                $a->created_at,
                ucfirst(str_replace('_', ' ', $a->status)),
            ];
        })->toArray();
    }

    private function mapMode(?string $mode): string
    {
        return match ($mode) {
            'FT', 'full_time' => 'Full Time',
            'PT', 'part_time' => 'Part Time',
            'FT-Startup'      => 'Startup Based Ph.D',
            'Integrated'      => 'Integrated PG + Ph.D',
            default           => $mode ?? '',
        };
    }
}
