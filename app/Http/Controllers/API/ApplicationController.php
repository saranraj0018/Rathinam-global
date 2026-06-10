<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $applications = Application::with([
            'user',
            'languages',
            'educations',
            'services',
            'projects',
            'courses',
            'aspirations',
            'documents',
            'enclosures'
        ])
            ->where('payment_status','paid')
            ->where('status','submitted')
            ->get()
            ->map(function ($application) {
                return [
                    'id' => $application->id,
                    'application_no' => $application->application_no,
                    'current_step' => $application->current_step,
                    'status' => $application->status,
                    'payment_status' => $application->payment_status,
                    'eligibility_qualified' => $application->eligibility_qualified,
                    'eligibility_exam' => $application->eligibility_exam,
                    // Programme
                    'school' => $application->school,
                    'discipline' => $application->discipline,
                    'specialization' => $application->specialization,
                    'specialization_other' => $application->specialization_other,
                    'engineering_stream' => $application->engineering_stream,
                    'programme_mode' => $application->programme_mode,

                    // Personal
                    'full_name' => $application->full_name,
                    'dob' => $application->dob,
                    'age' => $application->age,
                    'gender' => $application->gender,
                    'nationality' => $application->nationality,
                    'religion' => $application->religion,
                    'community' => $application->community,
                    'father_name' => $application->father_name,
                    'mother_name' => $application->mother_name,
                    'mobile' => $application->mobile,
                    'email' => $application->email,
                    'single_girl_child' => $application->single_girl_child,
                    'enclosures_confirm' => $application->enclosures_confirm,
                    'differently_abled' => $application->differently_abled,
                    // Address
                    'address_current' => $application->address_current,
                    'address_same' => $application->address_same,
                    'address_permanent' => $application->address_permanent,
                    'declaration' => $application->declaration,

                    // Relations
                    'languages' => $application->languages,
                    'educations' => $application->educations,
                    'services' => $application->services,
                    'projects' => $application->projects,
                    'courses' => $application->courses,
                    'aspirations' => $application->aspirations,
                    'enclosures' => $application->enclosures,
                    'documents' => $application->documents->map(function ($document) {
                        return [
                            'id' => $document->id,
                            'document_type' => $document->document_type,
                            'file_name' => $document->file_name,
                            'mime_type' => $document->mime_type,
                            'file_size' => $document->file_size,
                            'file_path' => asset('storage/' . $document->file_path),
                        ];
                    }),

                    'submitted_at' => $application->submitted_at,
                    'created_at' => $application->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Paid Application data fetched successfully',
            'count' => $applications->count(),
            'data' => $applications,
        ]);
    }
}
