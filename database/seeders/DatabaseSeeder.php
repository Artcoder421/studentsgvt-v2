<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Announcement;
use App\Models\CampusEvent;
use App\Models\Candidate;
use App\Models\CharityCampaign;
use App\Models\CharityContribution;
use App\Models\Community;
use App\Models\CommunityMessage;
use App\Models\Course;
use App\Models\Election;
use App\Models\HelpTicket;
use App\Models\HelpTicketReply;
use App\Models\Leader;
use App\Models\LostFoundItem;
use App\Models\SiteSetting;
use App\Models\SportsActivity;
use App\Models\TimerAnnouncement;
use App\Models\User;
use App\Models\Venue;
use App\Models\VenueBooking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Editable Site Settings (Motto, Vision, Mission, Branding, Contact)
        $settings = [
            ['org_name', 'Students Organization of the National Institute of Transport', 'identity', 'Organization Full Name'],
            ['org_short_name', 'SONIT', 'identity', 'Short Name / Acronym'],
            ['institution_name', 'National Institute of Transport (NIT)', 'identity', 'Institution Name'],
            ['tagline', 'Empowering Tomorrow\'s Transport, Logistics, Aviation & Engineering Leaders', 'identity', 'Hero Tagline'],
            ['motto', 'Unity, Integrity, and Academic Excellence in Motion.', 'identity', 'Organization Motto'],
            ['vision', 'To be the benchmark student governance organization in East Africa, championing student welfare, digital transparency, academic excellence, and transformative leadership across all transport and engineering disciplines.', 'identity', 'Organization Vision'],
            ['mission', 'To faithfully represent every NIT student, safeguard welfare and equity, cultivate vibrant course-based communities, facilitate transparent democratic elections, and partner with Institute leadership to elevate campus life.', 'identity', 'Organization Mission'],
            ['history', 'Rooted in the heritage of the National Institute of Transport (re-established by Act of Parliament No. 24 of 1982 in Mabibo, Dar es Salaam), SONIT serves thousands of certificate, diploma, bachelor\'s, and postgraduate students across Logistics, Aviation, Maritime, Engineering, and Computing faculties.', 'identity', 'About & History'],
            ['email', 'office@sonit.nit.ac.tz', 'contact', 'Official Email'],
            ['phone', '+255 22 240 0148', 'contact', 'Office Telephone'],
            ['emergency_phone', '+255 754 000 911', 'contact', '24/7 Student Welfare Hotline'],
            ['address', 'SONIT Secretariat Building, Mabibo Campus, P.O. Box 705, Dar es Salaam, Tanzania', 'contact', 'Campus Address'],
            ['office_hours', 'Monday – Friday: 08:00 – 17:00 EAT | Saturday: 09:00 – 13:00 EAT', 'contact', 'Office Hours'],
            ['founded_year', '1983', 'identity', 'Institute Operational Year'],
        ];

        foreach ($settings as [$key, $value, $group, $label]) {
            SiteSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'group' => $group,
                'label' => $label,
            ]);
        }

        // 2. Real National Institute of Transport (NIT) Tanzania Courses
        $coursesData = [
            // Faculty of Logistics and Business Studies
            ['BLTM', "Bachelor's Degree in Logistics and Transport Management", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Comprehensive training in multimodal transport planning, fleet operations, freight forwarding, and supply chain strategy.'],
            ['BPLM', "Bachelor's Degree in Procurement and Logistics Management", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Strategic sourcing, public procurement law, contract administration, and global logistics operations.'],
            ['BSPLM', "Bachelor's Degree in Shipping and Port Logistics Management", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Maritime economics, port terminal operations, customs clearance, and international sea freight logistics.'],
            ['BDRRTLM', "Bachelor's Degree in Road and Railway Transport Logistics Management", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Specialized degree in Standard Gauge Railway (SGR) operations, highway corridors, and intermodal freight hubs.'],
            ['BATF', "Bachelor's Degree in Accounting and Transport Finance", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Financial accounting, transport infrastructure financing, auditing, and fiscal management for mobility enterprises.'],
            ['BBA', "Bachelor's Degree in Business Administration", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Enterprise management, entrepreneurship, organizational leadership, and commercial operations.'],
            ['BHRM', "Bachelor's Degree in Human Resource Management", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Talent management, labour law, industrial relations, and strategic workforce development.'],
            ['BMPR', "Bachelor's Degree in Marketing and Public Relations", 'bachelor', 'Faculty of Logistics & Business Studies', 3, 'Corporate communications, brand strategy, digital marketing, and stakeholder relations.'],

            // Faculty of Computing, Information and Communication Technology
            ['BIT', "Bachelor's Degree in Information Technology", 'bachelor', 'Faculty of Computing & ICT', 3, 'Software engineering, enterprise networks, intelligent transport systems, cybersecurity, and database architecture.'],
            ['BCS', "Bachelor's Degree in Computer Science", 'bachelor', 'Faculty of Computing & ICT', 3, 'Algorithms, artificial intelligence, distributed systems, and high-assurance computing platforms.'],
            ['BEMIT', "Bachelor's Degree in Education with Mathematics and IT", 'bachelor', 'Faculty of Computing & ICT', 3, 'Pedagogical excellence combined with advanced applied mathematics and modern educational technology.'],

            // Faculty of Aviation and Transport Engineering
            ['BAME', "Bachelor's Degree in Aircraft Maintenance Engineering", 'bachelor', 'Faculty of Aviation & Transport Engineering', 4, 'Airframe, avionics, propulsion maintenance, and TCAA/ICAO aviation safety compliance at CoEATO.'],
            ['BAM', "Bachelor's Degree in Aviation Management", 'bachelor', 'Faculty of Aviation & Transport Engineering', 3, 'Airport operations, airline economics, air traffic coordination, and civil aviation regulation.'],
            ['BAE', "Bachelor's Degree in Automobile Engineering", 'bachelor', 'Faculty of Aviation & Transport Engineering', 4, 'Automotive powertrain, diagnostics, vehicle dynamics, and electric/hybrid mobility systems.'],
            ['BME', "Bachelor's Degree in Mechanical Engineering", 'bachelor', 'Faculty of Aviation & Transport Engineering', 4, 'Thermodynamics, transport machinery design, manufacturing systems, and structural mechanics.'],
            ['BCRE', "Bachelor's Degree in Civil and Railway Engineering", 'bachelor', 'Faculty of Aviation & Transport Engineering', 4, 'Railway track engineering, bridge structures, geotechnical design, and urban transit infrastructure.'],
            ['BNAME', "Bachelor's Degree in Naval Architecture and Marine Engineering", 'bachelor', 'Faculty of Aviation & Transport Engineering', 4, 'Ship design, marine propulsion, hydrodynamics, and offshore maritime engineering.'],

            // Ordinary Diplomas (NTA Level 4-6)
            ['DLTM', 'Ordinary Diploma in Logistics and Transport Management', 'diploma', 'Faculty of Logistics & Business Studies', 3, 'Foundational technical and operational competence in transport scheduling and logistics coordination.'],
            ['DIT', 'Ordinary Diploma in Information Technology', 'diploma', 'Faculty of Computing & ICT', 3, 'Hands-on web development, network administration, and IT support systems.'],
            ['DAME', 'Ordinary Diploma in Aircraft Maintenance Engineering', 'diploma', 'Faculty of Aviation & Transport Engineering', 3, 'Practical aircraft inspection, hangar maintenance procedures, and aviation workshop engineering.'],
            ['DAE', 'Ordinary Diploma in Automobile Engineering', 'diploma', 'Faculty of Aviation & Transport Engineering', 3, 'Vehicle diagnostics, engine overhaul, auto-electrical systems, and fleet workshop management.'],

            // Master's Degrees
            ['MLTM', 'Master of Logistics and Transport Management', 'masters', 'Directorate of Postgraduate Studies', 2, 'Executive postgraduate research and strategic policy leadership in regional transport corridors.'],
            ['MSPLM', "Master's in Shipping and Port Logistics Management", 'masters', 'Directorate of Postgraduate Studies', 2, 'Advanced maritime logistics, blue economy governance, and international port competitiveness.'],
        ];

        $coursesByCode = [];
        foreach ($coursesData as [$code, $name, $level, $faculty, $duration, $desc]) {
            $coursesByCode[$code] = Course::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'level' => $level,
                    'faculty' => $faculty,
                    'duration_years' => $duration,
                    'description' => $desc,
                    'is_active' => true,
                ]
            );
        }

        // 3. Seed Users with Strict RBAC Roles
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@sonit.nit.ac.tz'],
            [
                'name' => 'Dr. Kelvin M. Nyerere',
                'reg_no' => 'NIT/ADMIN/2020/0001',
                'password' => Hash::make('SuperAdmin@2026'),
                'role' => User::ROLE_SUPER_ADMIN,
                'course_id' => $coursesByCode['BIT']->id,
                'year_joined' => 2020,
                'academic_level' => 'System Governance',
                'phone' => '+255 713 100 001',
                'bio' => 'SONIT Super Administrator overseeing role governance, digital election integrity, and institutional portal operations.',
            ]
        );

        $sonitAdmin = User::updateOrCreate(
            ['email' => 'admin@sonit.nit.ac.tz'],
            [
                'name' => 'Hon. Baraka J. Mwakyusa',
                'reg_no' => 'NIT/BLTM/2021/0002',
                'password' => Hash::make('Admin@2026'),
                'role' => User::ROLE_ADMIN,
                'course_id' => $coursesByCode['BLTM']->id,
                'year_joined' => 2021,
                'academic_level' => 'Third Year',
                'phone' => '+255 754 200 102',
                'bio' => 'SONIT Cabinet Executive managing student services, campus announcements, welfare, and course communities.',
            ]
        );

        $bltmCr = User::updateOrCreate(
            ['email' => 'bltm.cr@sonit.nit.ac.tz'],
            [
                'name' => 'Gabriel Mabamba Hamisi',
                'reg_no' => 'NIT/BLTM/2022/4805',
                'password' => Hash::make('ClassRep@2026'),
                'role' => User::ROLE_CLASS_REP,
                'course_id' => $coursesByCode['BLTM']->id,
                'year_joined' => 2022,
                'academic_level' => 'Third Year',
                'phone' => '+255 762 480 500',
                'bio' => 'Elected Class Representative for Bachelor of Logistics and Transport Management (BLTM).',
            ]
        );

        $bitCr = User::updateOrCreate(
            ['email' => 'bit.cr@sonit.nit.ac.tz'],
            [
                'name' => 'Joseph Michael Ndulu',
                'reg_no' => 'NIT/BIT/2022/4567',
                'password' => Hash::make('ClassRep@2026'),
                'role' => User::ROLE_CLASS_REP,
                'course_id' => $coursesByCode['BIT']->id,
                'year_joined' => 2022,
                'academic_level' => 'Third Year',
                'phone' => '+255 714 456 700',
                'bio' => 'Elected Class Representative for Bachelor of Information Technology (BIT).',
            ]
        );

        $studentsData = [
            ['Hawa Ramadhan Hemed', 'student@sonit.nit.ac.tz', 'NIT/BLTM/2023/7658', 'BLTM', 2023, 'Second Year', '+255 755 765 801'],
            ['Aisha Mwinyi Kassim', 'aisha.kassim@sonit.nit.ac.tz', 'NIT/BIT/2023/5012', 'BIT', 2023, 'Second Year', '+255 712 501 234'],
            ['Felix John Mkwawa', 'felix.mkwawa@sonit.nit.ac.tz', 'NIT/BPLM/2022/1235', 'BPLM', 2022, 'Third Year', '+255 768 123 500'],
            ['Omar Issa Mohamed', 'omar.mohamed@sonit.nit.ac.tz', 'NIT/BATF/2022/1290', 'BATF', 2022, 'Third Year', '+255 784 129 011'],
            ['Neema Paul Mcharo', 'neema.mcharo@sonit.nit.ac.tz', 'NIT/BAME/2023/8765', 'BAME', 2023, 'Second Year', '+255 756 876 544'],
            ['Moses Paul Kimario', 'moses.kimario@sonit.nit.ac.tz', 'NIT/BSPLM/2022/2347', 'BSPLM', 2022, 'Third Year', '+255 715 234 788'],
            ['Sabrina Mohammed Mzee', 'sabrina.mzee@sonit.nit.ac.tz', 'NIT/BBA/2023/1987', 'BBA', 2023, 'Second Year', '+255 764 198 722'],
            ['Kelvin Amos Kaseko', 'kelvin.kaseko@sonit.nit.ac.tz', 'NIT/BLTM/2022/6542', 'BLTM', 2022, 'Third Year', '+255 759 654 200'],
        ];

        $seededStudents = [];
        foreach ($studentsData as [$name, $email, $reg, $code, $year, $level, $phone]) {
            $seededStudents[$reg] = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'reg_no' => $reg,
                    'password' => Hash::make('Student@2026'),
                    'role' => User::ROLE_STUDENT,
                    'course_id' => $coursesByCode[$code]->id,
                    'year_joined' => $year,
                    'academic_level' => $level,
                    'phone' => $phone,
                ]
            );
        }

        // 4. Default Course Communities for EVERY NIT Course (CR seat reserved; vacant seats managed by Admins!)
        $assignedCrs = [
            'BLTM' => $bltmCr->id,
            'BIT' => $bitCr->id,
        ];

        $courseCommunities = [];
        foreach ($coursesByCode as $code => $course) {
            $crId = $assignedCrs[$code] ?? null;
            $comm = Community::updateOrCreate(
                ['slug' => Str::slug($code.'-official')],
                [
                    'name' => "{$code} Official Course Community",
                    'description' => "Default official academic and welfare channel for all {$course->name} ({$code}) students. ".($crId ? 'Moderated by your elected Class Representative and SONIT Admin.' : 'CR seat currently reserved — actively moderated by SONIT Administration until a Class Representative is registered.'),
                    'type' => 'course_default',
                    'course_id' => $course->id,
                    'cr_user_id' => $crId,
                    'created_by' => $superAdmin->id,
                    'is_official' => true,
                    'is_active' => true,
                ]
            );
            $courseCommunities[$code] = $comm;

            // Attach superAdmin & sonitAdmin as admins; attach CR if assigned
            $comm->members()->syncWithoutDetaching([
                $superAdmin->id => ['member_role' => 'admin', 'joined_at' => now()],
                $sonitAdmin->id => ['member_role' => 'admin', 'joined_at' => now()],
            ]);
            if ($crId) {
                $comm->members()->syncWithoutDetaching([
                    $crId => ['member_role' => 'cr', 'joined_at' => now()],
                ]);
            }
        }

        // Attach seeded students to their respective default course community
        foreach ($seededStudents as $student) {
            $courseCode = $student->course->code;
            if (isset($courseCommunities[$courseCode])) {
                $courseCommunities[$courseCode]->members()->syncWithoutDetaching([
                    $student->id => ['member_role' => 'member', 'joined_at' => now()],
                ]);
            }
        }

        // Course-scoped sub-communities created by CRs
        $bltmStudyGroup = Community::updateOrCreate(
            ['slug' => 'bltm-final-year-research-hub'],
            [
                'name' => 'BLTM Final Year Research & Field Attachment Hub',
                'description' => 'Sub-community created by the BLTM Class Representative for dissertation supervision updates, TPA/TRC port attachment letters, and study notes.',
                'type' => 'course_group',
                'course_id' => $coursesByCode['BLTM']->id,
                'cr_user_id' => $bltmCr->id,
                'created_by' => $bltmCr->id,
                'is_official' => false,
                'is_active' => true,
            ]
        );
        $bltmStudyGroup->members()->syncWithoutDetaching([
            $bltmCr->id => ['member_role' => 'cr', 'joined_at' => now()],
            $seededStudents['NIT/BLTM/2023/7658']->id => ['member_role' => 'member', 'joined_at' => now()],
            $seededStudents['NIT/BLTM/2022/6542']->id => ['member_role' => 'member', 'joined_at' => now()],
        ]);

        $bitDevHub = Community::updateOrCreate(
            ['slug' => 'bit-software-engineering-guild'],
            [
                'name' => 'BIT Software Engineering & Cyber Lab',
                'description' => 'Code reviews, hackathons, capstone project teams, and networking lab sessions for NIT Information Technology students.',
                'type' => 'course_group',
                'course_id' => $coursesByCode['BIT']->id,
                'cr_user_id' => $bitCr->id,
                'created_by' => $bitCr->id,
                'is_official' => false,
                'is_active' => true,
            ]
        );
        $bitDevHub->members()->syncWithoutDetaching([
            $bitCr->id => ['member_role' => 'cr', 'joined_at' => now()],
            $seededStudents['NIT/BIT/2023/5012']->id => ['member_role' => 'member', 'joined_at' => now()],
        ]);

        // 5 General Campus Channels
        $generalChannels = [
            ['General Campus Square', 'general-campus-square', 'All-NIT town square for cross-faculty discussions, campus updates, and student community dialogue.'],
            ['Academics, Timetables & Exams', 'academics-timetables-exams', 'Official exam schedules, supplementary updates, library hours, and academic peer support across all faculties.'],
            ['Aviation, Maritime & Logistics Careers', 'aviation-maritime-careers', 'Industry internships, Tanzania Ports Authority, Air Tanzania, TRC SGR opportunities, and alumni mentorship.'],
            ['Freshers Orientation & Campus Guide', 'freshers-orientation-guide', 'Welcome hub for first-year students navigating SIMS registration, HESLB loans, Mabibo hostels, and campus venues.'],
            ['SONIT Innovation & Entrepreneurship', 'sonit-innovation-hub', 'Student founders, transport-tech prototypes, pitch competitions, and investor readiness networking.'],
        ];

        $generalModels = [];
        foreach ($generalChannels as [$name, $slug, $desc]) {
            $ch = Community::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'description' => $desc,
                    'type' => 'general',
                    'course_id' => null,
                    'cr_user_id' => null,
                    'created_by' => $superAdmin->id,
                    'is_official' => true,
                    'is_active' => true,
                ]
            );
            $ch->members()->syncWithoutDetaching([
                $superAdmin->id => ['member_role' => 'admin', 'joined_at' => now()],
                $sonitAdmin->id => ['member_role' => 'admin', 'joined_at' => now()],
                $bltmCr->id => ['member_role' => 'member', 'joined_at' => now()],
                $seededStudents['NIT/BLTM/2023/7658']->id => ['member_role' => 'member', 'joined_at' => now()],
            ]);
            $generalModels[$slug] = $ch;
        }

        // Seed realistic messages in communities
        if (CommunityMessage::count() === 0) {
            CommunityMessage::create([
                'community_id' => $courseCommunities['BLTM']->id,
                'user_id' => $bltmCr->id,
                'body' => 'Welcome to the official BLTM Course Community! All Semester II lecture room updates, logistics field trip notices, and CR announcements will be pinned here.',
                'is_pinned' => true,
            ]);
            CommunityMessage::create([
                'community_id' => $courseCommunities['BLTM']->id,
                'user_id' => $seededStudents['NIT/BLTM/2023/7658']->id,
                'body' => 'Thank you CR! Could you please confirm the venue for Friday\'s Multimodal Transport Logistics seminar?',
            ]);
            CommunityMessage::create([
                'community_id' => $courseCommunities['BLTM']->id,
                'user_id' => $bltmCr->id,
                'body' => 'Confirmed with the Faculty office — Friday\'s seminar will be held at Lecture Block A Auditorium starting at 10:00 AM sharp.',
            ]);
            CommunityMessage::create([
                'community_id' => $courseCommunities['BAME']->id,
                'user_id' => $sonitAdmin->id,
                'body' => 'Notice from SONIT Administration: The BAME Official Community is active and currently moderated by the SONIT Cabinet while Class Representative nominations are underway. Hangar practical schedules have been uploaded.',
                'is_pinned' => true,
            ]);
            CommunityMessage::create([
                'community_id' => $generalModels['general-campus-square']->id,
                'user_id' => $sonitAdmin->id,
                'body' => 'Welcome to the newly upgraded SONIT Digital Governance Portal! Explore course communities, transparent e-elections, campus venues, accommodation services, and the 24/7 Student Help Desk.',
                'is_pinned' => true,
            ]);
        }

        // 5. Official Leadership Directory (using our cinematically edited photos!)
        $leaders = [
            [
                'name' => 'Eng. Dr. Prosper L. Mgaya',
                'position' => 'Rector, National Institute of Transport',
                'category' => 'institute',
                'department' => 'Office of the Rector',
                'bio' => 'Leading the National Institute of Transport as a premier Center of Excellence in Aviation, Maritime, Railway, and Logistics education across East and Southern Africa.',
                'quote' => 'Our students are the engineers and strategists who will power Africa\'s connected transport future.',
                'photo' => 'images/portraits/rector.jpg',
                'email' => 'rector@nit.ac.tz',
                'phone' => '+255 22 240 0148',
                'office_location' => 'Administration Block, 3rd Floor',
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'name' => 'Hon. Stavius Alkadi',
                'position' => 'President, SONIT Student Government',
                'category' => 'executive',
                'department' => 'SONIT Executive Office',
                'bio' => 'Championing accountable student leadership, modern digital campus services, hostel welfare expansion, and strong dialogue between students and Institute management.',
                'quote' => 'Governance succeeds when every student\'s voice is heard, protected, and translated into measurable action.',
                'photo' => 'images/portraits/president.jpg',
                'email' => 'president@sonit.nit.ac.tz',
                'phone' => '+255 754 321 101',
                'office_location' => 'SONIT Secretariat, Room 101',
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'name' => 'Hon. Beatrice E. Mwakalinga',
                'position' => 'Vice President, SONIT Student Government',
                'category' => 'executive',
                'department' => 'SONIT Executive Office & Academic Affairs',
                'bio' => 'Overseeing student academic quality, gender equity initiatives, accommodation standards, charity outreach, and faculty representation across all NIT programmes.',
                'quote' => 'When we invest in student communities and welfare today, we elevate the entire National Institute of Transport.',
                'photo' => 'images/portraits/vice_president.jpg',
                'email' => 'vicepresident@sonit.nit.ac.tz',
                'phone' => '+255 714 654 202',
                'office_location' => 'SONIT Secretariat, Room 102',
                'sort_order' => 3,
                'is_featured' => true,
            ],
            [
                'name' => 'Hon. Janeth Johnson',
                'position' => 'Prime Minister & Cabinet Coordinator',
                'category' => 'ministry',
                'department' => 'Ministry of Student Affairs',
                'bio' => 'Coordinates SONIT Cabinet ministries including Education, Loans & HESLB, Accommodation, Sports, and Health Services.',
                'quote' => 'Service delivery with speed, empathy, and accountability.',
                'photo' => null,
                'email' => 'pm@sonit.nit.ac.tz',
                'phone' => '+255 765 890 303',
                'office_location' => 'SONIT Secretariat, Room 105',
                'sort_order' => 4,
                'is_featured' => false,
            ],
            [
                'name' => 'Hon. Robert Nziku',
                'position' => 'Minister of Finance & Resource Mobilization',
                'category' => 'ministry',
                'department' => 'Ministry of Finance',
                'bio' => 'Manages transparent student government budgeting, charity fund audits, and sponsorship partnerships.',
                'quote' => 'Every shilling entrusted to SONIT is accounted for openly.',
                'photo' => null,
                'email' => 'finance@sonit.nit.ac.tz',
                'phone' => '+255 788 112 404',
                'office_location' => 'SONIT Secretariat, Room 108',
                'sort_order' => 5,
                'is_featured' => false,
            ],
        ];

        foreach ($leaders as $leader) {
            Leader::updateOrCreate(['name' => $leader['name']], $leader);
        }

        // 6. Top-of-Homepage Timer Countdown Announcements
        TimerAnnouncement::updateOrCreate(
            ['title' => 'SONIT Annual Mabibo Orphanage & Welfare Charity Drive'],
            [
                'subtitle' => 'Join 1,400+ NIT students contributing towards school supplies, medical aid, and tuition relief before the deadline.',
                'category' => 'charity',
                'starts_at' => now()->subDays(2),
                'ends_at' => now()->addDays(6)->addHours(14),
                'cta_label' => 'Contribute Now',
                'cta_url' => '/charity',
                'target_amount' => 5000000,
                'raised_amount' => 3450000,
                'is_active' => true,
                'priority' => 10,
                'created_by' => $superAdmin->id,
            ]
        );

        TimerAnnouncement::updateOrCreate(
            ['title' => 'SONIT 2026/2027 General Elections — Online Ballot Closes Soon'],
            [
                'subtitle' => 'Cast your verified vote for President and your Course Parliament Representative securely via the E-Election Portal.',
                'category' => 'election',
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(3)->addHours(8),
                'cta_label' => 'Go to Ballot',
                'cta_url' => '/elections',
                'is_active' => true,
                'priority' => 8,
                'created_by' => $superAdmin->id,
            ]
        );

        // 7. Announcements (with Urgent Flag for live Badge Counter)
        $announcements = [
            [
                'title' => 'URGENT: Semester II Examination Timetable & Clearance Deadline',
                'slug' => 'urgent-semester-ii-exam-timetable-clearance',
                'excerpt' => 'All NIT students must complete SIMS fee clearance and print examination cards at least 48 hours before papers commence.',
                'content' => "The Office of the Deputy Rector (Academic, Research and Consultancy) in coordination with SONIT Academic Ministry informs all Certificate, Ordinary Diploma, Bachelor's, and Master's students that the Final Examination Timetable for Semester II is now live.\n\nKey Action Items:\n1. Verify all registered modules on the NIT SIMS Portal.\n2. Resolve any HESLB loan disbursement queries immediately through the SONIT Help Desk.\n3. Carry both your valid NIT Student ID and printed Exam Card to every examination venue.\n\nStudents encountering portal discrepancies should open an urgent ticket in the Student Help Desk before Friday at 16:00 EAT.",
                'category' => 'academic',
                'is_urgent' => true,
                'is_pinned' => true,
                'cover_image' => 'images/campus/building.jpg',
                'published_at' => now()->subHours(3),
                'author_id' => $sonitAdmin->id,
            ],
            [
                'title' => 'SONIT General Elections 2026/2027 — Voting Portal Officially Open',
                'slug' => 'sonit-general-elections-2026-2027-open',
                'excerpt' => 'Verified electronic voting is live for President, Vice President, and Course Parliament Representatives.',
                'content' => "The SONIT Electoral Commission announces that the 2026/2027 General Election voting portal is officially open.\n\nEvery registered student can cast one verified vote for Campus President and one vote for their respective Course Parliament Representative. All votes are protected by single-ballot database constraints and full audit integrity.\n\nReview candidate manifestos carefully and exercise your democratic right today.",
                'category' => 'election',
                'is_urgent' => true,
                'is_pinned' => true,
                'cover_image' => 'images/campus/hero_gate.jpg',
                'published_at' => now()->subHours(10),
                'author_id' => $superAdmin->id,
            ],
            [
                'title' => 'Extended 24-Hour Main Library Study Wing & CoEATO Wi-Fi Upgrade',
                'slug' => 'extended-library-hours-and-wifi-upgrade',
                'excerpt' => 'Following SONIT advocacy, the NIT Main Library reading wing now stays open until midnight with upgraded high-speed fibre.',
                'content' => "We are pleased to share that Institute Management has approved SONIT's proposal to extend Main Library operating hours to 08:00 – 00:00 daily during the pre-examination period.\n\nAdditionally, high-capacity Wi-Fi 6 access points have been commissioned across Lecture Blocks A–D, the CoEATO Wing, and the Student Study Pavilion.",
                'category' => 'welfare',
                'is_urgent' => false,
                'is_pinned' => false,
                'cover_image' => 'images/campus/coeato.jpg',
                'published_at' => now()->subDays(1),
                'author_id' => $sonitAdmin->id,
            ],
            [
                'title' => 'Inter-Faculty Transport Cup & Annual Sports Gala Registration',
                'slug' => 'inter-faculty-transport-cup-sports-gala',
                'excerpt' => 'Football, Basketball, Volleyball, Netball, and Athletics trials kick off next week at the NIT Main Sports Complex.',
                'content' => "The SONIT Ministry of Sports, Culture and Entertainment invites student athletes from all faculties to register for the annual Inter-Faculty Transport Cup.\n\nRegister directly through the Sports & Recreation portal to join your faculty squad or sign up for individual track events.",
                'category' => 'sports',
                'is_urgent' => false,
                'is_pinned' => false,
                'cover_image' => 'images/campus/students.jpg',
                'published_at' => now()->subDays(2),
                'author_id' => $sonitAdmin->id,
            ],
        ];

        foreach ($announcements as $item) {
            Announcement::updateOrCreate(['slug' => $item['slug']], $item);
        }

        // 8. Active Election Cycle & Approved Candidates
        $election = Election::updateOrCreate(
            ['academic_year' => '2026/2027'],
            [
                'title' => 'SONIT General Student Government Elections 2026/2027',
                'description' => 'Transparent, single-ballot electronic elections for the SONIT Executive Presidency and Course Parliament Representatives.',
                'is_enabled' => true,
                'applications_open' => true,
                'voting_open' => true,
                'results_published' => true,
                'voting_starts_at' => now()->subDay(),
                'voting_ends_at' => now()->addDays(3),
            ]
        );

        $candidates = [
            [
                'fullname' => 'Stavius Alkadi',
                'reg_no' => 'NIT/BLTM/2022/3001',
                'course_id' => $coursesByCode['BLTM']->id,
                'academic_level' => 'Third Year',
                'position' => 'President',
                'motto' => 'Digital Transparency & Unstoppable Student Welfare',
                'manifesto' => '1. Expand verified affordable Mabibo student hostels.\n2. Maintain 24/7 digital Help Desk SLA for HESLB & SIMS issues.\n3. Establish an annual Innovation & Logistics Career Fair connecting NIT students with TPA, TRC, and ATCL.',
                'photo' => 'images/portraits/president.jpg',
                'status' => 'approved',
            ],
            [
                'fullname' => 'Beatrice E. Mwakalinga',
                'reg_no' => 'NIT/BIT/2022/3044',
                'course_id' => $coursesByCode['BIT']->id,
                'academic_level' => 'Third Year',
                'position' => 'President',
                'motto' => 'Inclusive Excellence, Modern Campus Infrastructure',
                'manifesto' => '1. Upgrade campus-wide high-speed Wi-Fi and 24-hour digital labs.\n2. Champion women in aviation, maritime, and transport engineering scholarships.\n3. Publish quarterly audited SONIT financial statements on the portal.',
                'photo' => 'images/portraits/vice_president.jpg',
                'status' => 'approved',
            ],
            [
                'fullname' => 'Gabriel Mabamba Hamisi',
                'reg_no' => 'NIT/BLTM/2022/4805',
                'course_id' => $coursesByCode['BLTM']->id,
                'user_id' => $bltmCr->id,
                'academic_level' => 'Third Year',
                'position' => 'Parliament Representative',
                'motto' => 'Stronger Voice for Every Logistics & Transport Scholar',
                'manifesto' => 'Advocating for industry field attachments with Dar es Salaam Port and TRC, updated lecture timetables, and active BLTM study communities.',
                'photo' => null,
                'status' => 'approved',
            ],
            [
                'fullname' => 'Kelvin Amos Kaseko',
                'reg_no' => 'NIT/BLTM/2022/6542',
                'course_id' => $coursesByCode['BLTM']->id,
                'user_id' => $seededStudents['NIT/BLTM/2022/6542']->id,
                'academic_level' => 'Third Year',
                'position' => 'Parliament Representative',
                'motto' => 'Practical Solutions, Timely Academic Support',
                'manifesto' => 'Focusing on timely coursework feedback, digital past-paper archives for BLTM modules, and transport industry guest lectures.',
                'photo' => null,
                'status' => 'approved',
            ],
            [
                'fullname' => 'Joseph Michael Ndulu',
                'reg_no' => 'NIT/BIT/2022/4567',
                'course_id' => $coursesByCode['BIT']->id,
                'user_id' => $bitCr->id,
                'academic_level' => 'Third Year',
                'position' => 'Parliament Representative',
                'motto' => 'Code, Innovate, Represent',
                'manifesto' => 'Expanding evening ICT lab access, sponsoring NIT coding hackathons, and strengthening tech internship pipelines.',
                'photo' => null,
                'status' => 'approved',
            ],
            [
                'fullname' => 'Neema Paul Mcharo',
                'reg_no' => 'NIT/BAME/2023/8765',
                'course_id' => $coursesByCode['BAME']->id,
                'user_id' => $seededStudents['NIT/BAME/2023/8765']->id,
                'academic_level' => 'Second Year',
                'position' => 'Parliament Representative',
                'motto' => 'Precision & Safety in Aviation Leadership',
                'manifesto' => 'Enhancing CoEATO workshop tool availability, TCAA exam preparation workshops, and safety gear support.',
                'photo' => null,
                'status' => 'approved',
            ],
        ];

        foreach ($candidates as $cand) {
            Candidate::updateOrCreate(
                ['election_id' => $election->id, 'reg_no' => $cand['reg_no'], 'position' => $cand['position']],
                $cand
            );
        }

        // 9. Campus Venues
        $venues = [
            ['NIT Multi-Purpose Hall (MPH)', 'MPH-01', 'Central Campus Pavilion', 1200, 'auditorium', 'HD Projection, Concert PA Sound, Stage Lighting, Air Conditioning, Live Stream Rig', 'Flagship assembly venue for SONIT general meetings, graduation symposiums, and investor forums.', 'images/campus/building.jpg', 'available'],
            ['CoEATO Aviation Auditorium', 'COE-101', 'Center of Excellence in Aviation & Transport Operations', 350, 'auditorium', '4K Smart Display, Conference Audio, High-Speed Wi-Fi, Tiered Executive Seating', 'State-of-the-art auditorium inside the CoEATO wing for aviation seminars, guest lectures, and international conferences.', 'images/campus/coeato.jpg', 'available'],
            ['Aviation Training Hangar & Simulator Wing', 'COE-H02', 'CoEATO Technical Wing', 120, 'hangar', 'Aircraft Training Bays, Flight Simulators, Safety Briefing Gallery', 'Hands-on technical hangar used by BAME, BAM, and engineering cohorts for practical demonstrations.', 'images/campus/students.jpg', 'booked'],
            ['Lecture Block A — Hall A1', 'LBA-01', 'Academic Block A', 260, 'lecture_hall', 'Dual Projectors, Wireless Mic, Power Outlets, Fibre Wi-Fi', 'Primary large lecture amphitheatre for core BLTM, BPLM, and BIT plenary modules.', 'images/campus/building.jpg', 'available'],
            ['Computing & Innovation Lab 1', 'ICT-201', 'ICT Complex, 2nd Floor', 85, 'lab', '85 Workstations, Gigabit LAN, Smart Whiteboard, Backup Power', 'Modern computing laboratory hosting software engineering labs, hackathons, and Cisco networking practicals.', 'images/campus/building.jpg', 'available'],
            ['SONIT Executive Boardroom', 'SNT-104', 'SONIT Secretariat', 40, 'meeting', 'Conference Table, Video Conferencing Camera, Whiteboard, Refreshment Station', 'Dedicated meeting room for Class Representatives, Parliamentary Committees, and club executives.', 'images/campus/hero_gate.jpg', 'available'],
            ['NIT Main Athletics & Football Arena', 'SPT-01', 'East Campus Grounds', 2000, 'sports', 'Full-Size Pitch, Basketball & Volleyball Courts, Floodlights, Spectator Stands', 'Home ground for the Inter-Faculty Transport Cup, intramural leagues, and student fitness sessions.', 'images/campus/hero_gate.jpg', 'available'],
        ];

        $venueModels = [];
        foreach ($venues as [$name, $code, $building, $cap, $cat, $amen, $desc, $img, $status]) {
            $venueModels[$code] = Venue::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'building' => $building,
                    'capacity' => $cap,
                    'category' => $cat,
                    'amenities' => $amen,
                    'description' => $desc,
                    'image' => $img,
                    'status' => $status,
                ]
            );
        }

        if (VenueBooking::count() === 0) {
            VenueBooking::create([
                'venue_id' => $venueModels['COE-101']->id,
                'user_id' => $bltmCr->id,
                'event_title' => 'BLTM Supply Chain & SGR Logistics Symposium',
                'organization_or_course' => 'BLTM Student Cohort',
                'booking_date' => now()->addDays(5)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '17:00',
                'expected_attendees' => 240,
                'purpose' => 'Guest seminar with Tanzania Railways Corporation operations engineers.',
                'status' => 'approved',
                'admin_notes' => 'Approved by SONIT Venues Office. PA system reserved.',
            ]);
        }

        // 10. Accommodation Services
        $accommodations = [
            [
                'name' => 'NIT Main Campus Hostels (Blocks A & B)',
                'slug' => 'nit-main-campus-hostels',
                'type' => 'on_campus_hostel',
                'gender_policy' => 'mixed',
                'location' => 'Inside NIT Mabibo Campus',
                'distance_from_campus' => 'On Campus (2 min walk to lecture blocks)',
                'price_per_semester' => 180000,
                'available_rooms' => 24,
                'total_rooms' => 180,
                'amenities' => '24/7 Campus Security, High-Speed NIT Wi-Fi, Reading Lounge, Clean Running Water, Backup Generator',
                'contact_person' => 'Warden Office — Mabibo Campus',
                'contact_phone' => '+255 22 240 0152',
                'description' => 'Official Institute-managed on-campus halls of residence offering secure, affordable accommodation with immediate access to the Main Library and lecture theatres.',
                'image' => 'images/campus/building.jpg',
                'is_verified' => true,
            ],
            [
                'name' => 'Mabibo Executive Student Residence',
                'slug' => 'mabibo-executive-student-residence',
                'type' => 'verified_off_campus',
                'gender_policy' => 'female',
                'location' => 'Mabibo External, 350m from NIT Main Gate',
                'distance_from_campus' => '4 min walk to NIT Main Gate',
                'price_per_semester' => 320000,
                'available_rooms' => 12,
                'total_rooms' => 64,
                'amenities' => 'Biometric Gate Access, En-Suite Study Rooms, Fibre Wi-Fi, Shared Kitchenette, Laundry Area, Borehole Water',
                'contact_person' => 'Madam Grace Mollel',
                'contact_phone' => '+255 754 881 200',
                'description' => 'SONIT-inspected private female student residence featuring quiet study desks, reliable water supply, and 24-hour perimeter security.',
                'image' => 'images/campus/hero_gate.jpg',
                'is_verified' => true,
            ],
            [
                'name' => 'Ubungo Riverside Scholars Hostel',
                'slug' => 'ubungo-riverside-scholars-hostel',
                'type' => 'verified_off_campus',
                'gender_policy' => 'male',
                'location' => 'Ubungo Riverside, Dar es Salaam',
                'distance_from_campus' => '8 min walk / 3 min bajaji to NIT Gate',
                'price_per_semester' => 280000,
                'available_rooms' => 18,
                'total_rooms' => 90,
                'amenities' => '24/7 Security Guard, Study Hall, Wi-Fi Included, Cafeteria Access, Solar Backup Lighting',
                'contact_person' => 'Mr. Juma Ramadhani',
                'contact_phone' => '+255 713 442 990',
                'description' => 'Well-maintained SONIT-verified off-campus hostel popular with BIT, BAE, and BLTM students seeking quiet group study spaces.',
                'image' => 'images/campus/coeato.jpg',
                'is_verified' => true,
            ],
            [
                'name' => 'CoEATO Postgraduate & Aviation Suites',
                'slug' => 'coeato-postgraduate-aviation-suites',
                'type' => 'shared_apartment',
                'gender_policy' => 'mixed',
                'location' => 'Mabibo Loyola Road',
                'distance_from_campus' => '6 min walk to NIT Main Gate',
                'price_per_semester' => 450000,
                'available_rooms' => 7,
                'total_rooms' => 30,
                'amenities' => 'Single Self-Contained Rooms, Air Conditioning, Dedicated Fibre Wi-Fi, Parking, 24/7 CCTV',
                'contact_person' => 'SONIT Accommodation Desk',
                'contact_phone' => '+255 762 900 310',
                'description' => 'Self-contained studio apartments tailored for Master\'s students, aviation trainees, and final-year researchers.',
                'image' => 'images/campus/building.jpg',
                'is_verified' => true,
            ],
        ];

        foreach ($accommodations as $acc) {
            Accommodation::updateOrCreate(['slug' => $acc['slug']], $acc);
        }

        // 11. Help Desk Sample Ticket
        if (HelpTicket::count() === 0) {
            $ticket = HelpTicket::create([
                'ticket_number' => 'SONIT-2026-0001',
                'user_id' => $seededStudents['NIT/BLTM/2023/7658']->id,
                'category' => 'finance_heslb',
                'subject' => 'HESLB Semester II Meal & Accommodation Allocation Verification',
                'description' => 'My SIMS portal still shows pending clearance for the second HESLB tranche even though the disbursement list was posted. Requesting SONIT Loans Ministry assistance to follow up with the Bursar office.',
                'priority' => 'high',
                'status' => 'in_progress',
                'is_anonymous' => false,
                'assigned_to' => $sonitAdmin->id,
            ]);

            HelpTicketReply::create([
                'help_ticket_id' => $ticket->id,
                'user_id' => $sonitAdmin->id,
                'message' => 'Received with priority. The SONIT Ministry of Loans has submitted your registration number (NIT/BLTM/2023/7658) in Batch 4 to the Institute Loans Officer. Expect SIMS clearance update within 24 hours.',
                'is_staff_reply' => true,
            ]);
        }

        // 12. Lost & Found Items
        if (LostFoundItem::count() === 0) {
            LostFoundItem::create([
                'user_id' => $sonitAdmin->id,
                'type' => 'found',
                'item_name' => 'NIT Student ID Card & CRDB Debit Card',
                'category' => 'id_card',
                'description' => 'Found inside a navy lanyard near the Multi-Purpose Hall entrance after the morning plenary session. Safely held at the SONIT Secretariat Desk.',
                'location' => 'Multi-Purpose Hall (MPH) Entrance',
                'date_occurred' => now()->subDays(1)->toDateString(),
                'contact_info' => 'SONIT Secretariat Desk — Room 101 (+255 22 240 0148)',
                'status' => 'open',
            ]);
            LostFoundItem::create([
                'user_id' => $seededStudents['NIT/BIT/2023/5012']->id,
                'type' => 'lost',
                'item_name' => 'HP USB-C 65W Laptop Charger & Grey Mouse',
                'category' => 'electronics',
                'description' => 'Left on Row 3 workstation desk in Computing Lab 1 during the afternoon database systems practical.',
                'location' => 'ICT Complex — Computing Lab 1',
                'date_occurred' => now()->subDays(2)->toDateString(),
                'contact_info' => 'Aisha Kassim (+255 712 501 234)',
                'status' => 'open',
            ]);
            LostFoundItem::create([
                'user_id' => $bltmCr->id,
                'type' => 'found',
                'item_name' => 'Casio Scientific Calculator (fx-991EX)',
                'category' => 'accessories',
                'description' => 'Found on Lecture Block A Hall A1 front podium after Quantitative Methods tutorial.',
                'location' => 'Lecture Block A — Hall A1',
                'date_occurred' => now()->subDays(3)->toDateString(),
                'contact_info' => 'BLTM CR Office (+255 762 480 500)',
                'status' => 'open',
            ]);
        }

        // 13. Campus Events
        $events = [
            [
                'title' => 'NIT Transport & Logistics Innovation Summit 2026',
                'slug' => 'nit-transport-logistics-innovation-summit-2026',
                'description' => 'Flagship forum uniting students, industry leaders from TPA, TRC, Air Tanzania, and technology investors to showcase student-built transport & supply-chain innovations.',
                'category' => 'academic',
                'venue_name' => 'CoEATO Aviation Auditorium',
                'starts_at' => now()->addDays(9)->setTime(9, 0),
                'ends_at' => now()->addDays(9)->setTime(16, 30),
                'image' => 'images/campus/coeato.jpg',
                'is_featured' => true,
                'created_by' => $superAdmin->id,
            ],
            [
                'title' => 'Inter-Faculty Transport Cup — Grand Opening Match',
                'slug' => 'inter-faculty-transport-cup-opening',
                'description' => 'Faculty of Logistics & Business Studies takes on Faculty of Aviation & Engineering in the opening clash of the 2026 Transport Cup.',
                'category' => 'sports',
                'venue_name' => 'NIT Main Athletics & Football Arena',
                'starts_at' => now()->addDays(5)->setTime(15, 30),
                'ends_at' => now()->addDays(5)->setTime(18, 0),
                'image' => 'images/campus/hero_gate.jpg',
                'is_featured' => true,
                'created_by' => $sonitAdmin->id,
            ],
            [
                'title' => 'Mabibo Community Outreach & Charity Handover Day',
                'slug' => 'mabibo-charity-handover-day',
                'description' => 'Student volunteers gather at SONIT Square before departing to deliver collected educational supplies, meals, and medical kits to partner children\'s centres in Mabibo.',
                'category' => 'charity',
                'venue_name' => 'SONIT Student Centre Plaza',
                'starts_at' => now()->addDays(7)->setTime(8, 30),
                'ends_at' => now()->addDays(7)->setTime(14, 0),
                'image' => 'images/campus/students.jpg',
                'is_featured' => true,
                'created_by' => $sonitAdmin->id,
            ],
            [
                'title' => 'SONIT Presidential Debate & Manifesto Night',
                'slug' => 'sonit-presidential-debate-2026',
                'description' => 'Live moderated debate with all approved Presidential candidates answering student questions on welfare, hostels, academic quality, and digital governance.',
                'category' => 'governance',
                'venue_name' => 'NIT Multi-Purpose Hall (MPH)',
                'starts_at' => now()->addDays(2)->setTime(17, 0),
                'ends_at' => now()->addDays(2)->setTime(20, 0),
                'image' => 'images/campus/building.jpg',
                'is_featured' => false,
                'created_by' => $superAdmin->id,
            ],
        ];

        foreach ($events as $ev) {
            CampusEvent::updateOrCreate(['slug' => $ev['slug']], $ev);
        }

        // 14. Sports & Recreation
        $sports = [
            ['NIT Transport FC (Men\'s Football)', 'football', 'Juma Suleiman', 'Mon, Wed & Fri · 16:30 – 18:30', 'NIT Main Pitch', 'Premier inter-university football squad representing NIT in the Dar es Salaam Higher Learning Institutions League.', 'NIT Transport FC vs UDSM Lions', now()->addDays(5)->setTime(16, 0), 'Won 3 – 1 vs IFM Stars'],
            ['NIT SkyHawks Basketball', 'basketball', 'Kelvin Kaseko', 'Tue & Thu · 17:00 – 19:00', 'NIT Outdoor Basketball Court', 'Competitive men\'s and women\'s basketball teams competing in the RBA University Division.', 'NIT SkyHawks vs DIT Engineers', now()->addDays(8)->setTime(17, 30), 'Won 68 – 61 vs Ardhi Titans'],
            ['NIT Queens Netball Club', 'netball', 'Neema Mcharo', 'Mon & Thu · 16:30 – 18:00', 'East Campus Netball Court', 'Dynamic women\'s netball team focused on fitness, teamwork, and national university championships.', 'NIT Queens vs CBE Royals', now()->addDays(10)->setTime(16, 0), 'Won 42 – 35 vs TIA United'],
            ['NIT Spikers Volleyball', 'volleyball', 'Moses Kimario', 'Tue, Thu & Sat · 16:00 – 18:00', 'Campus Volleyball Arena', 'Open to both beginners and varsity players across all faculties.', 'Inter-Faculty Volleyball Mini-League', now()->addDays(6)->setTime(15, 0), '2nd Place — Dar University Open'],
        ];

        foreach ($sports as [$name, $type, $captain, $schedule, $venue, $desc, $match, $matchDate, $result]) {
            SportsActivity::updateOrCreate(
                ['name' => $name],
                [
                    'sport_type' => $type,
                    'captain_name' => $captain,
                    'schedule' => $schedule,
                    'venue' => $venue,
                    'description' => $desc,
                    'upcoming_match' => $match,
                    'match_date' => $matchDate,
                    'last_result' => $result,
                    'is_open_for_joining' => true,
                    'is_active' => true,
                ]
            );
        }

        // 15. Charity & Student Welfare Campaigns
        $charity1 = CharityCampaign::updateOrCreate(
            ['slug' => 'mabibo-orphanage-back-to-school-drive'],
            [
                'title' => 'Mabibo Orphanage & Community Back-to-School Drive',
                'beneficiary' => 'Tumaini & Faraja Children\'s Centres, Mabibo',
                'description' => 'SONIT\'s flagship semester charity initiative providing textbooks, uniforms, clean water tanks, and nutrition packs to 180 vulnerable children in our surrounding Mabibo community.',
                'target_amount' => 5000000,
                'raised_amount' => 3450000,
                'ends_at' => now()->addDays(6)->addHours(14),
                'payment_instructions' => 'M-Pesa / Tigo Pesa Lipa Namba: 5589021 (SONIT CHARITY TRUST) or CRDB Account: 0150442198800. Submit your contribution pledge & reference below for transparent public tallying.',
                'image' => 'images/campus/students.jpg',
                'status' => 'active',
                'created_by' => $sonitAdmin->id,
            ]
        );

        CharityCampaign::updateOrCreate(
            ['slug' => 'sonit-emergency-student-tuition-solidarity-fund'],
            [
                'title' => 'SONIT Emergency Student Tuition & Medical Solidarity Fund',
                'beneficiary' => 'Financially Vulnerable Continuing NIT Students',
                'description' => 'A transparent student-backed solidarity reserve assisting high-performing NIT scholars facing sudden bereavement or acute medical emergencies so no student drops out over short-term hardship.',
                'target_amount' => 8000000,
                'raised_amount' => 4820000,
                'ends_at' => now()->addDays(21),
                'payment_instructions' => 'NMB Account: 20110089451 (SONIT WELFARE SOLIDARITY) or Airtel Money Merchant: 441209.',
                'image' => 'images/campus/coeato.jpg',
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ]
        );

        if (CharityContribution::count() === 0) {
            CharityContribution::create([
                'charity_campaign_id' => $charity1->id,
                'user_id' => $bltmCr->id,
                'donor_name' => 'BLTM Third Year Class Collective',
                'amount' => 450000,
                'payment_reference' => 'MPESA-Q89X22L',
                'message' => 'Proudly supporting our Mabibo neighbours!',
                'status' => 'verified',
            ]);
            CharityContribution::create([
                'charity_campaign_id' => $charity1->id,
                'user_id' => $bitCr->id,
                'donor_name' => 'BIT Innovation Guild',
                'amount' => 300000,
                'payment_reference' => 'CRDB-778102',
                'message' => 'Education transforms lives.',
                'status' => 'verified',
            ]);
        }
    }
}
