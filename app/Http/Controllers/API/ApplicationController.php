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
            ->get()
            ->map(function ($application) {

                return [
                    'id' => $application->id,
                    'application_no' => $application->application_no,
                    'current_step' => $application->current_step,
                    'status' => $application->status,
                    'payment_status' => $application->payment_status,

                    // Programme
                    'school' => $application->school,
                    'discipline' => $application->discipline,
                    'specialization' => $application->specialization,
                    'specialization_other' => $application->specialization_other,
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

                    // Image URL
                    'photo' => $application->photo
                        ? asset('storage/' . $application->photo)
                        : null,

                    'community_certificate' => $application->community_certificate
                        ? asset('storage/' . $application->community_certificate)
                        : null,

                    'disability_certificate' => $application->disability_certificate
                        ? asset('storage/' . $application->disability_certificate)
                        : null,

                    'eligibility_certificate' => $application->eligibility_certificate
                        ? asset('storage/' . $application->eligibility_certificate)
                        : null,

                    'summary_document' => $application->summary_document
                        ? asset('storage/' . $application->summary_document)
                        : null,

                    'noc_document' => $application->noc_document
                        ? asset('storage/' . $application->noc_document)
                        : null,

                    'service_certificate' => $application->service_certificate
                        ? asset('storage/' . $application->service_certificate)
                        : null,

                    'equivalence_certificate' => $application->equivalence_certificate
                        ? asset('storage/' . $application->equivalence_certificate)
                        : null,

                    // Address
                    'address_current' => $application->address_current,
                    'address_same' => $application->address_same,
                    'address_permanent' => $application->address_permanent,

                    // Relations
                    'languages' => $application->languages,

                    'educations' => $application->educations,

                    'services' => $application->services,

                    'projects' => $application->projects,

                    'courses' => $application->courses,

                    'aspirations' => $application->aspirations,

                    'enclosures' => $application->enclosures,

                    // Documents with full URL
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
