<?php

/*
|--------------------------------------------------------------------------
| Ph.D. Scholar Application — Reference Data (Annexure-1 + option lists)
|--------------------------------------------------------------------------
|
| Single source of truth for the form's dropdowns. The same array is shared
| with the browser (as JSON) to power the cascading School -> Discipline ->
| Specialization selects, and is available server-side for the backend team
| to validate submitted values against.
|
| NOTE ON SPECIALIZATIONS: In the source document (Annexure-1) some
| specializations were shared across disciplines using "a & b" style
| prefixes. They have been DENORMALIZED here: every discipline carries its
| own complete list. Disciplines with no listed specialization use an empty
| array — the UI then falls back to a free-text "Other (please specify)"
| field so the applicant is never blocked.
|
*/

return [

    'programme_modes' => [
        'FT'         => 'Full Time',
        'FT-Startup' => 'Startup Based Ph.D',
        'PT'         => 'Part Time',
        'Integrated' => 'Integrated PG + Ph.D',
    ],

    'genders' => ['Male', 'Female', 'Transgender'],

    'language_skills' => ['R' => 'Read', 'W' => 'Write', 'S' => 'Speak', 'U' => 'Understand'],

    'communities' => ['OC', 'EWS', 'BC', 'BCM', 'MBC', 'DNC', 'SC', 'SCA', 'ST'],

    // Education rows (Q: Educational Qualification table)
    'education_levels' => [
        'sslc'     => 'SSLC',
        'diploma'  => 'Diploma',
        'hsc'      => 'HSC',
        'bachelor' => "Bachelor's Degree",
        'master'   => "Master's Degree",
        'mphil'    => 'M.Phil.',
        'others'   => 'Others',
    ],

    // National / State level eligibility examinations
    'eligibility_exams' => [
        'SLET', 'SET', 'UGC-NET', 'CSIR-NET', 'ASRB-NET', 'GATE',
        'ICMR-JRF', 'DBT-JRF (BET)', 'INSPIRE', 'ICAR-JRF', 'JEST', 'AICTE-QIP',
    ],

    'mandatory_courses' => [
        'research_methodology' => 'Research Methodology',
        'publication_ethics'   => 'Research and Publication Ethics',
    ],

    'career_aspirations' => [
        'Employment (Govt.)', 'Employment (Private)', 'Self-employment',
        'Start-up or Entrepreneurship', 'Family Business', 'Research',
        'Consultancy', 'Teaching', 'Any other',
    ],

    // List of Enclosures checklist. Each item auto-ticks from the upload named
    // in `source` (done earlier in the form); `source => null` means manual.
    // `pt_only` items appear only for the Part-Time programme mode.
    'enclosures' => [
        ['key' => 'sslc_marksheet',     'label' => 'Self-Attested copy of SSLC Mark Sheet',                                            'pt_only' => false, 'source' => 'education[sslc][marksheet]'],
        ['key' => 'plus2_marksheet',    'label' => 'Self-Attested copy of +2 Mark Sheet',                                              'pt_only' => false, 'source' => 'education[hsc][marksheet]'],
        ['key' => 'ug_degree',          'label' => 'Self-Attested copy of UG Degree (or) Provisional Certificate',                     'pt_only' => false, 'source' => 'education[bachelor][marksheet]'],
        ['key' => 'pg_marksheets',      'label' => 'Self-Attested copy of PG Mark Sheets / Consolidated mark sheet',                   'pt_only' => false, 'source' => 'education[master][marksheet]'],
        ['key' => 'pg_degree',          'label' => 'Self-Attested copy of PG Degree (or) Provisional Certificate',                     'pt_only' => false, 'source' => 'education[master][marksheet]'],
        ['key' => 'mphil_degree',       'label' => 'Self-Attested copy of M.Phil. Degree (or) Provisional Certificate',               'pt_only' => false, 'source' => 'education[mphil][marksheet]'],
        ['key' => 'community_cert',     'label' => 'Self-Attested copy of Community Certificate (OC/EWS/BC/BCM/MBC/DNC/SC/SCA/ST)',    'pt_only' => false, 'source' => 'community_cert'],
        ['key' => 'noc',                'label' => 'No Objection Certificate (NOC) from employer / head of institution',              'pt_only' => true,  'source' => 'noc_document'],
        ['key' => 'service_cert',       'label' => 'Service Certificate from employer / head of institution',                          'pt_only' => true,  'source' => 'service_certificate'],
        ['key' => 'equivalence_cert',   'label' => 'Equivalence Certificate in case of foreign degree (produce at admission)',         'pt_only' => false, 'source' => null],
    ],

    // Annexure-1 — School / Discipline / Specialization
    'schools' => [
        [
            'name' => 'Quantum Science, Computing & AI',
            'disciplines' => [
                [
                    'name' => 'Computer Science',
                    'specializations' => [
                        'Data Science & Analytics',
                        'AI, GAI, ML, DL, NLP',
                        'Information and Communication',
                        'Image Processing',
                        'Networks',
                        'Cyber and Information Security',
                        'Digital Forensics',
                        'Cloud, Quantum, Parallel, Mobile, Ubiquitous / Invisible and Green Computing',
                        'Internet of Things (IoT)',
                        'Robotics & Automation',
                        'Blockchain Technology',
                        'AR, VR and MR',
                        'Computer Vision',
                        'Human Computer Interaction (HCI)',
                        'Computational Intelligence',
                        'Digital Twin Technology',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Engineering & Technology',
            'disciplines' => [
                [
                    'name' => 'Engineering',
                    'specializations' => [
                        'Computer Science Engineering',
                        'Mechanical Engineering',
                        'Civil Engineering',
                        'Electronics and Communication Engineering',
                        'Environmental Engineering',
                        'Electrical and Electronics Engineering',
                    ],
                ],
                [
                    'name' => 'Technology',
                    'specializations' => [
                        'Information Technology',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Business & Commerce',
            'disciplines' => [
                [
                    'name' => 'Management',
                    'specializations' => [
                        'Finance', 'Marketing', 'Logistics', 'Banking & Insurance', 'Fintech',
                        'HRM', 'International Business', 'Taxation', 'e-Commerce & Digital Business',
                        'Tourism and Hospitality', 'Management', 'Business Analytics',
                        'Strategic Management', 'Organizational Development',
                        'Innovation-oriented Management Areas', 'Agri-Business',
                        'Operations with industry-focused research tools',
                        'AI-enabled Management Practices', 'Applied Business Research',
                        'Sustainable Business & ESG Management',
                        'Digital Entrepreneurship & Innovation Management',
                        'Behavioural Economics / Consumer Psychology',
                        'Holistic Wellness & Human Capital Management',
                    ],
                ],
                [
                    'name' => 'Commerce',
                    'specializations' => [
                        'Finance', 'Marketing', 'Logistics', 'Banking & Insurance', 'Fintech',
                        'HRM', 'International Business', 'Taxation', 'e-Commerce & Digital Business',
                    ],
                ],
                [
                    'name' => 'Public Administration',
                    'specializations' => [
                        'Social Policy', 'e-Governance', 'Social Welfare Administration',
                        'Gender', 'Rural Development', 'Local Self-governance (Panchayatraj)',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Fashion Design, Media & Performing Arts',
            'disciplines' => [
                [
                    'name' => 'Costume Design & Fashion',
                    'specializations' => [
                        'Textile and Apparel Design',
                        'Textile and Clothing, Fashion and Textile',
                        'Textile and Apparel Management',
                        'Polymer, Fiber and Textile Sciences',
                        'Apparel and Textile Technology',
                        'Textile Science and Apparel Design',
                        'Textile Technology, Sustainable Textile',
                        'Textile Engineering and Science',
                    ],
                ],
                [
                    'name' => 'Communication',
                    'specializations' => [
                        'Mass', 'Visual', 'Corporate', 'Development', 'Political',
                        'Intercultural', 'Broadcast', 'Integrated Marketing', 'Health', 'Educational',
                    ],
                ],
                [
                    'name' => 'Journalism',
                    'specializations' => [
                        'Advertising & Brand', 'Social Media', 'Animation, VFX & Gaming',
                        'Journalism Studies', 'Film, OTT and Television Studies',
                        'Digital & New Media', 'Advertising & Public Relations',
                        'Media Psychology', 'AI and Media', 'Communication Technology',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Liberal Arts and Science',
            'disciplines' => [
                [
                    'name' => 'Tamil',
                    'specializations' => [
                        'பக்தி இலக்கியம்', 'வைணவம்', 'நீதி இலக்கியம்', 'சிற்றிலக்கியம்', 'நவீன இலக்கியம்',
                    ],
                ],
                [
                    'name' => 'English',
                    'specializations' => [
                        'English Literature', 'Comparative Literature',
                        'English Language Teaching', 'Linguistics',
                    ],
                ],
                [
                    'name' => 'Mathematics',
                    'specializations' => [
                        'Topology', 'Graph Theory', 'Fluid Dynamics', 'Number Theory',
                        'Operational Research', 'Computational Mathematics',
                    ],
                ],
                [
                    'name' => 'Physics',
                    'specializations' => [
                        'Plasma Physics', 'Material Science', 'Optics', 'Quantum Modelling',
                        'Astrophysics', 'Crystal Growth', 'Thin Film Technology',
                        'Soft-Matter Physics', 'Bio-Physics', 'Electro-Chemical Applications',
                    ],
                ],
                [
                    'name' => 'Psychology',
                    'specializations' => [
                        'Clinical and Health', 'Counselling', 'Behavioural', 'Organizational',
                        'Educational', 'Developmental', 'Forensic', 'Applied', 'Sports',
                        'Psychosocial Studies', 'Cross-Cultural',
                        'Cognitive and Behavioural Neuroscience', 'Yoga', 'Law',
                        'Positive Psychology', 'Psycholinguistics', 'Environmental',
                        'Cyber / Digital', 'Psychometrics', 'Military / Aviation',
                    ],
                ],
                [
                    'name' => 'Microbiology',
                    'specializations' => [
                        'Bacteriology', 'Virology', 'Mycology', 'Immunology',
                        'Molecular Biology', 'Genetic Engineering', 'Environmental', 'Food',
                    ],
                ],
                [
                    'name' => 'Chemistry',
                    'specializations' => [
                        'Environmental Pollutants Remediation',
                        'Fluorescent Materials as Biomarkers in Bio-imaging',
                        'Super-hydrophobic Textiles', 'Advanced Functional Materials',
                        'Nanocomposites', 'Electrochemistry', 'Semiconductor Materials',
                        'Textile Chemistry', 'Surface Modification',
                        'Functionalization of Silk and Cotton Fabrics',
                        'Inorganic-Organic Hybrid Materials', 'Bioinorganic Materials Chemistry',
                        'Polymer Nano-Composites', 'Corrosion Inhibitors',
                        'Photo and Chemo Degradation',
                    ],
                ],
            ],
        ],
        [
            'name' => 'Applied Biosciences, Food & Agritech',
            'disciplines' => [
                [
                    'name' => 'Biotechnology',
                    'specializations' => [
                        'Plant', 'Animal', 'Environmental', 'Nano',
                        'Microbial and Molecular Biology', 'Genetic Engineering', 'Food',
                        'Biodiversity', 'Conservation', 'Cancer Biology',
                    ],
                ],
                ['name' => 'Agritech', 'specializations' => []],
                ['name' => 'Nanoscience & Technology', 'specializations' => []],
            ],
        ],
        [
            'name' => 'Sustainability & Climate Studies',
            'disciplines' => [
                ['name' => 'Environmental Science & Technology', 'specializations' => []],
            ],
        ],
        [
            'name' => 'Sports & Health Science',
            'disciplines' => [
                ['name' => 'Physical Education', 'specializations' => []],
            ],
        ],
    ],

];
