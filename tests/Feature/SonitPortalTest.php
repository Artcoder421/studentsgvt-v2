<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Candidate;
use App\Models\CharityCampaign;
use App\Models\Community;
use App\Models\Course;
use App\Models\Election;
use App\Models\HelpTicket;
use App\Models\SiteSetting;
use App\Models\TimerAnnouncement;
use App\Models\User;
use App\Models\Venue;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SonitPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render_successfully_with_timers_and_portraits(): void
    {
        $routes = [
            '/',
            '/leadership',
            '/announcements',
            '/elections',
            '/elections/results',
            '/venues',
            '/accommodation',
            '/help-desk',
            '/lost-found',
            '/events',
            '/sports',
            '/charity',
            '/login',
            '/register',
        ];

        foreach ($routes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200);
        }

        // Logged-out users CANNOT see inside communities
        $this->get('/communities')->assertRedirect('/login');
        $this->get('/communities/bltm-official')->assertRedirect('/login');

        $home = $this->get('/');
        $home->assertSee('SONIT 2026/2027 General Elections — Online Ballot Closes Soon');
        $home->assertSee('Mabibo Orphanage & Community Charity Tour');
        $home->assertSee('images/nit_logo_badge.png');
        $home->assertSee('images/portraits/rector.jpg');
        $home->assertSee('images/portraits/president.jpg');
        $home->assertSee('images/portraits/vice_president.jpg');
        $home->assertSee('images/campus/hero_gate.jpg');

        // Charity page shows What, Where, When, Account Number & Contacts (no in-system payment form)
        $charity = $this->get('/charity');
        $charity->assertSee('CRDB Bank: 0150442198800');
        $charity->assertSee('What We Are Planning To Do');
        $charity->assertDontSee('Log Your Contribution / Pledge');
    }

    public function test_login_works_with_nit_reg_no_and_blocks_invalid_password(): void
    {
        $bad = $this->post('/login', [
            'identifier' => 'NIT/BLTM/2023/7658',
            'password' => 'WrongPassword',
        ]);
        $bad->assertSessionHasErrors('identifier');
        $this->assertGuest();

        $good = $this->post('/login', [
            'identifier' => 'NIT/BLTM/2023/7658',
            'password' => 'Student@2026',
        ]);
        $good->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_student_registration_auto_joins_default_course_community(): void
    {
        $bame = Course::where('code', 'BAME')->firstOrFail();

        $resp = $this->post('/register', [
            'name' => 'Juma Aviation Scholar',
            'reg_no' => 'NIT/BAME/2026/9901',
            'email' => 'juma.bame@student.nit.ac.tz',
            'course_id' => $bame->id,
            'year_joined' => 2026,
            'academic_level' => 'First Year',
            'password' => 'StrongPass@2026',
            'password_confirmation' => 'StrongPass@2026',
        ]);

        $resp->assertRedirect('/dashboard');
        $user = User::where('reg_no', 'NIT/BAME/2026/9901')->firstOrFail();
        $defaultComm = Community::where('type', 'course_default')->where('course_id', $bame->id)->firstOrFail();

        $this->assertTrue($defaultComm->hasMember($user));
    }

    public function test_rbac_enforcement_for_admin_and_super_admin_role_assignment(): void
    {
        $student = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $superAdmin = User::where('email', 'superadmin@sonit.nit.ac.tz')->firstOrFail();

        // Student cannot access admin dashboard
        $this->actingAs($student)->get('/admin')->assertStatus(403);

        // Regular admin CAN access admin dashboard, CANNOT change user roles
        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        $this->actingAs($admin)->post("/admin/users/{$student->id}/role", [
            'role' => 'admin',
            'course_id' => $student->course_id,
        ])->assertStatus(403);

        // Super admin CAN assign roles
        $this->actingAs($superAdmin)->post("/admin/users/{$student->id}/role", [
            'role' => 'class_rep',
            'course_id' => $student->course_id,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertEquals('class_rep', $student->fresh()->role);
    }

    public function test_cr_can_only_create_communities_for_own_course_and_admin_controls_vacant_cr_courses(): void
    {
        $bltmCr = User::where('reg_no', 'NIT/BLTM/2022/4805')->firstOrFail();
        $bitCourse = Course::where('code', 'BIT')->firstOrFail();
        $bltmCourse = Course::where('code', 'BLTM')->firstOrFail();
        $bameCourse = Course::where('code', 'BAME')->firstOrFail();
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();

        // BLTM CR CANNOT create a group for BIT (403)
        $this->actingAs($bltmCr)->post('/communities', [
            'name' => 'Unauthorized BIT Group',
            'description' => 'Should be blocked by RBAC.',
            'type' => 'course_group',
            'course_id' => $bitCourse->id,
        ])->assertStatus(403);

        // BLTM CR CANNOT create a general campus channel (403)
        $this->actingAs($bltmCr)->post('/communities', [
            'name' => 'Unauthorized General Channel',
            'description' => 'Should be blocked.',
            'type' => 'general',
        ])->assertStatus(403);

        // BLTM CR CAN create a sub-community for BLTM
        $this->actingAs($bltmCr)->post('/communities', [
            'name' => 'BLTM Maritime & SGR Study Group',
            'description' => 'Authorized study group for BLTM students.',
            'type' => 'course_group',
            'course_id' => $bltmCourse->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('communities', [
            'name' => 'BLTM Maritime & SGR Study Group',
            'course_id' => $bltmCourse->id,
            'cr_user_id' => $bltmCr->id,
        ]);

        // BAME default community exists with vacant CR seat and Admin can still lock/moderate it
        $bameComm = Community::where('type', 'course_default')->where('course_id', $bameCourse->id)->firstOrFail();
        $this->assertNull($bameComm->cr_user_id);

        $this->actingAs($admin)->post("/communities/{$bameComm->slug}/toggle-lock")->assertRedirect();
        $this->assertTrue($bameComm->fresh()->is_locked);
    }

    public function test_students_are_automatically_in_own_course_communities_and_cannot_see_other_courses(): void
    {
        $bltmStudent = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $bitStudent = User::where('reg_no', 'NIT/BIT/2023/5012')->firstOrFail();
        $bltmComm = Community::where('slug', 'bltm-official')->firstOrFail();
        $bitComm = Community::where('slug', 'bit-official')->firstOrFail();
        $bameComm = Community::where('slug', 'bame-official')->firstOrFail();

        // BLTM student visits /communities: sees BLTM, NEVER sees BIT or BAME
        $list = $this->actingAs($bltmStudent)->get('/communities');
        $list->assertStatus(200);
        $list->assertSee('BLTM Official Course Community');
        $list->assertDontSee('BIT Official Course Community');
        $list->assertDontSee('BAME Official Course Community');

        // Automatically enrolled in BLTM official community
        $this->assertTrue($bltmComm->hasMember($bltmStudent));

        // BLTM student can open BLTM community, CANNOT open or message BIT or BAME communities
        $this->actingAs($bltmStudent)->get("/communities/{$bltmComm->slug}")->assertStatus(200);
        $this->actingAs($bltmStudent)->get("/communities/{$bitComm->slug}")->assertStatus(403);
        $this->actingAs($bltmStudent)->get("/communities/{$bameComm->slug}")->assertStatus(403);
        $this->actingAs($bltmStudent)->post("/communities/{$bitComm->slug}/messages", [
            'body' => 'Should be blocked',
        ])->assertStatus(403);

        // BIT student sees BIT, never sees BLTM
        $this->actingAs($bitStudent)->get('/communities')
            ->assertSee('BIT Official Course Community')
            ->assertDontSee('BLTM Official Course Community');
        $this->actingAs($bitStudent)->get("/communities/{$bltmComm->slug}")->assertStatus(403);
    }

    public function test_elections_voting_prevents_duplicates_enforces_course_scope_and_respects_toggle(): void
    {
        $bltmStudent = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $election = Election::firstOrFail();
        $president = Candidate::where('position', 'President')->where('status', 'approved')->firstOrFail();
        $bltmPr = Candidate::where('position', 'Parliament Representative')
            ->where('course_id', $bltmStudent->course_id)
            ->firstOrFail();
        $bitCourse = Course::where('code', 'BIT')->firstOrFail();
        $bitPr = Candidate::where('position', 'Parliament Representative')
            ->where('course_id', $bitCourse->id)
            ->firstOrFail();

        // Cast valid Presidential vote
        $this->actingAs($bltmStudent)->post('/elections/vote', [
            'position' => 'President',
            'candidate_id' => $president->id,
        ])->assertRedirect('/elections');

        $this->assertDatabaseCount('votes', 1);

        // Duplicate Presidential vote rejected
        $this->actingAs($bltmStudent)->post('/elections/vote', [
            'position' => 'President',
            'candidate_id' => $president->id,
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 1);

        // BLTM student CANNOT vote for BIT PR candidate (403)
        $this->actingAs($bltmStudent)->post('/elections/vote', [
            'position' => 'Parliament Representative',
            'candidate_id' => $bitPr->id,
        ])->assertStatus(403);

        // BLTM student CAN vote for BLTM PR candidate
        $this->actingAs($bltmStudent)->post('/elections/vote', [
            'position' => 'Parliament Representative',
            'candidate_id' => $bltmPr->id,
        ])->assertRedirect('/elections');
        $this->assertDatabaseCount('votes', 2);

        // While election is active, top countdown strip prioritizes the Election countdown over Charity Tour
        $this->get('/')
            ->assertSee('SONIT 2026/2027 General Elections — Online Ballot Closes Soon');

        // Admin ends election immediately EVEN WHILE voting_ends_at countdown is still days in the future!
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $election->update(['voting_ends_at' => now()->addDays(4)]);
        $this->assertTrue($election->fresh()->isVotingActive());

        $this->actingAs($admin)->post("/admin/elections/{$election->id}/end")->assertRedirect();
        $this->assertTrue($election->fresh()->isEnded());
        $this->assertFalse($election->fresh()->isVotingActive());

        // Election countdown timer is automatically deactivated and Charity Tour takes top countdown strip
        $this->assertEquals(0, TimerAnnouncement::where('category', 'election')->where('is_active', true)->count());
        $this->get('/')
            ->assertDontSee('SONIT 2026/2027 General Elections — Online Ballot Closes Soon')
            ->assertSee('SONIT Annual Mabibo Orphanage & Charity Tour');

        // Student can no longer vote even though scheduled voting_ends_at is still in the future
        $otherStudent = User::where('reg_no', 'NIT/BIT/2023/5012')->firstOrFail();
        $this->actingAs($otherStudent)->post('/elections/vote', [
            'position' => 'President',
            'candidate_id' => $president->id,
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 2);

        // Student dashboard renders President portrait + editable Motto
        $dash = $this->actingAs($bltmStudent)->get('/dashboard');
        $dash->assertStatus(200);
        $dash->assertSee('images/portraits/president.jpg');
        $dash->assertSee(SiteSetting::getValue('motto'));
    }

    public function test_notification_badge_unread_tracking_and_editable_motto_vision_mission(): void
    {
        $student = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $initialUnread = $student->unreadAnnouncementsCount();
        $this->assertGreaterThan(0, $initialUnread);

        // Reading one announcement decrements unread badge count
        $ann = Announcement::where('is_published', true)->firstOrFail();
        $this->actingAs($student)->get("/announcements/{$ann->slug}")->assertStatus(200);
        $this->assertEquals($initialUnread - 1, $student->unreadAnnouncementsCount());

        // Mark all read sets count to 0
        $this->actingAs($student)->post('/announcements/mark-all-read')->assertRedirect();
        $this->assertEquals(0, $student->unreadAnnouncementsCount());

        // Admin edits Motto, Vision & Mission and sees it live on homepage
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $this->actingAs($admin)->post('/admin/settings', [
            'settings' => [
                'motto' => 'Forward Together Through Smart Transport Governance.',
                'vision' => 'A digitally connected NIT campus built on trust.',
                'mission' => 'Deliver transparent student services every day.',
            ],
        ])->assertRedirect();

        $this->get('/')->assertSee('Forward Together Through Smart Transport Governance.');
    }

    public function test_student_services_and_campus_life_workflows(): void
    {
        $student = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $venue = Venue::firstOrFail();
        $campaign = CharityCampaign::firstOrFail();

        // Venue booking
        $this->actingAs($student)->post('/venues/book', [
            'venue_id' => $venue->id,
            'event_title' => 'Logistics Coding Workshop',
            'organization_or_course' => 'BLTM',
            'booking_date' => now()->addDays(10)->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'expected_attendees' => 50,
            'purpose' => 'Hands-on analytics workshop.',
        ])->assertRedirect();

        // Help Desk ticket + Admin reply
        $this->actingAs($student)->post('/help-desk', [
            'category' => 'academic',
            'subject' => 'Course Unit Registration Query',
            'description' => 'Requesting assistance syncing Semester II elective credits.',
            'priority' => 'high',
        ])->assertRedirect();

        $ticket = HelpTicket::where('subject', 'Course Unit Registration Query')->firstOrFail();
        $this->actingAs($admin)->post("/help-desk/{$ticket->ticket_number}/reply", [
            'message' => 'Resolved with the Registrar office.',
            'status' => 'resolved',
        ])->assertRedirect();
        $this->assertEquals('resolved', $ticket->fresh()->status);

        // Charity pledge + Admin verification increments raised total
        $beforeRaised = $campaign->raised_amount;
        $this->actingAs($student)->post("/charity/{$campaign->slug}/pledge", [
            'donor_name' => 'Hawa Hemed',
            'amount' => 50000,
            'payment_reference' => 'MPESA-TEST123',
        ])->assertRedirect();

        $pledge = $campaign->contributions()->where('payment_reference', 'MPESA-TEST123')->firstOrFail();
        $this->actingAs($admin)->post("/admin/charity/contributions/{$pledge->id}/verify")->assertRedirect();
        $this->assertEquals($beforeRaised + 50000, $campaign->fresh()->raised_amount);

        // Admin creates a new top countdown timer
        $this->actingAs($admin)->post('/admin/timers', [
            'title' => 'Emergency Blood Donation Drive at NIT Plaza',
            'category' => 'charity',
            'ends_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
            'cta_label' => 'Join Drive',
            'cta_url' => '/charity',
            'priority' => 99,
        ])->assertRedirect();

        $this->assertTrue(TimerAnnouncement::where('title', 'Emergency Blood Donation Drive at NIT Plaza')->exists());
    }

    public function test_candidate_application_fee_interview_edit_delete_auto_promotion_and_logs(): void
    {
        $student = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $election = Election::firstOrFail();

        // 1. Student applies with email & phone -> sees unpaid status -> submits payment ref
        $this->actingAs($student)->post('/elections/apply', [
            'position' => 'General Secretary',
            'academic_level' => 'Second Year',
            'contact_email' => 'hawa.hemed@student.nit.ac.tz',
            'contact_phone' => '+255 755 765 801',
            'motto' => 'Efficient Student Records',
            'manifesto' => 'Modernizing parliamentary minutes and transparent committee tracking across NIT.',
        ])->assertRedirect('/dashboard');

        $app = Candidate::where('user_id', $student->id)->firstOrFail();
        $this->assertEquals('unpaid', $app->payment_status);

        $this->actingAs($student)->post("/elections/application/{$app->id}/payment", [
            'payment_reference' => 'MPESA-FEE-8891',
        ])->assertRedirect();
        $this->assertEquals('submitted', $app->fresh()->payment_status);

        // 2. Admin edits contestant name AND marks interview as FAILED (Did Not Pass Interview)
        $this->actingAs($admin)->put("/admin/candidates/{$app->id}", [
            'fullname' => 'Hawa R. Hemed (Edited)',
            'reg_no' => $app->reg_no,
            'position' => 'General Secretary',
            'contact_email' => 'hawa.hemed@student.nit.ac.tz',
            'contact_phone' => '+255 755 765 801',
            'motto' => 'Efficient Student Records',
            'payment_status' => 'verified',
            'interview_status' => 'failed',
            'interview_notes' => 'Minimum GPA requirement not met.',
        ])->assertRedirect();

        $this->assertEquals('Hawa R. Hemed (Edited)', $app->fresh()->fullname);
        $this->assertEquals('declined', $app->fresh()->status);

        // Student sees "Did Not Pass Interview" clearly on Dashboard & Elections page
        $this->actingAs($student)->get('/dashboard')
            ->assertSee('Did Not Pass Interview')
            ->assertSee('Minimum GPA requirement not met.');

        // 3. Admin deletes that contestant cleanly
        $this->actingAs($admin)->delete("/admin/candidates/{$app->id}")->assertRedirect();
        $this->assertDatabaseMissing('candidates', ['id' => $app->id]);

        // 4. Beatrice wins President vote -> Admin ends election -> Beatrice AUTOMATICALLY becomes Homepage President & gets SONIT President title on her account!
        $beatriceCand = Candidate::where('fullname', 'Beatrice E. Mwakalinga')->firstOrFail();
        $this->actingAs($student)->post('/elections/vote', [
            'position' => 'President',
            'candidate_id' => $beatriceCand->id,
        ])->assertRedirect('/elections');

        $this->actingAs($admin)->post("/admin/elections/{$election->id}/end")->assertRedirect();

        $beatriceUser = User::where('reg_no', 'NIT/BIT/2022/3044')->firstOrFail();
        $this->assertEquals('SONIT President', $beatriceUser->sonit_title);

        // Homepage & Beatrice's own logged-in Dashboard immediately show her as SONIT President without manual upload!
        $this->get('/')->assertSee('Hon. Beatrice E. Mwakalinga');
        $this->actingAs($beatriceUser)->get('/dashboard')->assertSee('SONIT President');

        // 5. Activity Logs & Visitor Traffic are STRICTLY Admin-only (guests, students, and CRs blocked)
        $this->assertGreaterThan(0, \App\Models\ActivityLog::count());
        $this->assertGreaterThan(0, \App\Models\VisitorLog::count());

        $bltmCr = User::where('reg_no', 'NIT/BLTM/2022/4805')->firstOrFail();
        $this->actingAs($student)->get('/admin/logs')->assertStatus(403);
        $this->actingAs($bltmCr)->get('/admin/logs')->assertStatus(403);
        $this->actingAs($student)->get('/dashboard')->assertDontSee('Unique Visitors (IP)');

        $this->actingAs($admin)->get('/admin')
            ->assertStatus(200)
            ->assertSee('Portal Traffic (Admin Only)');

        $this->actingAs($admin)->get('/admin/logs')
            ->assertStatus(200)
            ->assertSee('election_ended')
            ->assertSee('candidate_deleted');
    }

    public function test_student_profile_avatars_in_community_chat_and_admin_full_edit_delete_everything(): void
    {
        $student = User::where('reg_no', 'NIT/BLTM/2023/7658')->firstOrFail();
        $admin = User::where('email', 'admin@sonit.nit.ac.tz')->firstOrFail();
        $bltmComm = Community::where('slug', 'bltm-official')->firstOrFail();

        // Verify all admin consoles load cleanly (200 OK)
        foreach (['/admin/users', '/admin/courses', '/admin/timers', '/admin/announcements', '/admin/elections', '/admin/services', '/admin/campus-life', '/admin/settings', '/admin/logs'] as $adminUrl) {
            $this->actingAs($admin)->get($adminUrl)->assertStatus(200);
        }

        // 1. Student uploads profile photo -> posts in Community Chat -> photo renders in Community Chat & Sidebar
        $fakeAvatar = \Illuminate\Http\UploadedFile::fake()->image('student_avatar.jpg', 200, 200);
        $this->actingAs($student)->post('/profile', [
            'name' => $student->name,
            'phone' => '+255 755 111 222',
            'academic_level' => 'Second Year',
            'bio' => 'NIT Logistics Student',
            'avatar' => $fakeAvatar,
        ])->assertRedirect();

        $avatarPath = $student->fresh()->avatar;
        $this->assertNotNull($avatarPath);
        $this->assertStringStartsWith('uploads/avatars/', $avatarPath);

        $this->actingAs($student)->post("/communities/{$bltmComm->slug}/messages", [
            'body' => 'Hello BLTM classmates, testing my profile photo in chat!',
        ])->assertRedirect();

        $chatPage = $this->actingAs($student)->get("/communities/{$bltmComm->slug}");
        $chatPage->assertStatus(200)
            ->assertSee('Hello BLTM classmates, testing my profile photo in chat!')
            ->assertSee($avatarPath);

        // 2. Admin removes student's profile photo
        $this->actingAs($admin)->post("/admin/users/{$student->id}/remove-avatar")->assertRedirect();
        $this->assertNull($student->fresh()->avatar);
        $this->assertFileDoesNotExist(public_path($avatarPath));

        // 3. Admin edits student name/title and deletes a throwaway user
        $this->actingAs($admin)->put("/admin/users/{$student->id}", [
            'name' => 'Hawa Hemed Updated',
            'reg_no' => $student->reg_no,
            'email' => $student->email,
            'course_id' => $student->course_id,
            'sonit_title' => 'BLTM Welfare Coordinator',
            'is_active' => 1,
        ])->assertRedirect();
        $this->assertEquals('Hawa Hemed Updated', $student->fresh()->name);
        $this->assertEquals('BLTM Welfare Coordinator', $student->fresh()->sonit_title);

        $tempUser = User::create([
            'name' => 'Temp Student DeleteMe',
            'email' => 'temp.delete@student.nit.ac.tz',
            'reg_no' => 'NIT/TEMP/2026/0001',
            'password' => bcrypt('TempPass@2026'),
            'role' => User::ROLE_STUDENT,
            'is_active' => true,
        ]);
        $this->actingAs($admin)->delete("/admin/users/{$tempUser->id}")->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $tempUser->id]);

        // 4. Admin edits Leader name, removes Leader photo, and deletes a Leader
        $leader = \App\Models\Leader::firstOrFail();
        $this->actingAs($admin)->put("/admin/leaders/{$leader->id}", [
            'name' => 'Dr. Updated Leader Name',
            'position' => $leader->position,
            'category' => $leader->category,
            'remove_photo' => 1,
        ])->assertRedirect();
        $this->assertEquals('Dr. Updated Leader Name', $leader->fresh()->name);
        $this->assertNull($leader->fresh()->photo);

        $tempLeader = \App\Models\Leader::create([
            'name' => 'Temp Leader',
            'position' => 'Temporary Chair',
            'category' => 'ministry',
            'is_active' => true,
        ]);
        $this->actingAs($admin)->delete("/admin/leaders/{$tempLeader->id}")->assertRedirect();
        $this->assertDatabaseMissing('leaders', ['id' => $tempLeader->id]);

        // 5. Admin edits & deletes Announcements, Timers, Courses, Venues, Accommodations, Events, Sports, Charity, Tickets, Lost & Found
        $ann = Announcement::firstOrFail();
        $this->actingAs($admin)->put("/admin/announcements/{$ann->id}", [
            'title' => 'Updated Announcement Headline',
            'category' => 'academic',
            'content' => 'Updated full circular details.',
        ])->assertRedirect();
        $this->assertEquals('Updated Announcement Headline', $ann->fresh()->title);

        $timer = TimerAnnouncement::firstOrFail();
        $this->actingAs($admin)->put("/admin/timers/{$timer->id}", [
            'title' => 'Updated Countdown Banner Title',
            'category' => $timer->category,
            'ends_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
        ])->assertRedirect();
        $this->assertEquals('Updated Countdown Banner Title', $timer->fresh()->title);

        $course = Course::where('code', 'BAME')->firstOrFail();
        $this->actingAs($admin)->put("/admin/courses/{$course->id}", [
            'code' => 'BAME',
            'name' => 'Bachelor of Aircraft Maintenance Engineering (Updated)',
            'level' => 'bachelor',
            'faculty' => $course->faculty,
            'duration_years' => 4,
        ])->assertRedirect();
        $this->assertEquals('Bachelor of Aircraft Maintenance Engineering (Updated)', $course->fresh()->name);

        $venue = Venue::firstOrFail();
        $this->actingAs($admin)->put("/admin/venues/{$venue->id}", [
            'name' => 'Updated Main Auditorium',
            'code' => $venue->code,
            'building' => $venue->building,
            'capacity' => 500,
            'category' => 'auditorium',
            'status' => 'available',
        ])->assertRedirect();
        $this->assertEquals('Updated Main Auditorium', $venue->fresh()->name);

        $acc = \App\Models\Accommodation::firstOrFail();
        $this->actingAs($admin)->put("/admin/accommodations/{$acc->id}", [
            'name' => 'Updated Mabibo Hostel',
            'type' => $acc->type,
            'gender_policy' => $acc->gender_policy,
            'location' => $acc->location,
            'price_per_semester' => 280000,
            'available_rooms' => 12,
            'total_rooms' => 50,
        ])->assertRedirect();
        $this->assertEquals('Updated Mabibo Hostel', $acc->fresh()->name);

        $event = \App\Models\CampusEvent::firstOrFail();
        $this->actingAs($admin)->put("/admin/events/{$event->id}", [
            'title' => 'Updated Campus Summit',
            'category' => 'academic',
            'venue_name' => 'NIT Main Hall',
            'starts_at' => now()->addDays(6)->format('Y-m-d H:i:s'),
            'description' => 'Updated event details.',
        ])->assertRedirect();
        $this->assertEquals('Updated Campus Summit', $event->fresh()->title);

        $sport = \App\Models\SportsActivity::firstOrFail();
        $this->actingAs($admin)->put("/admin/sports/{$sport->id}", [
            'name' => 'NIT FC Updated',
            'sport_type' => 'football',
            'schedule' => 'Tue & Fri 16:30',
            'venue' => 'NIT Main Pitch',
        ])->assertRedirect();
        $this->assertEquals('NIT FC Updated', $sport->fresh()->name);

        $charity = CharityCampaign::firstOrFail();
        $this->actingAs($admin)->put("/admin/charity/{$charity->id}", [
            'title' => 'Updated Mabibo Charity Tour',
            'beneficiary' => 'Mabibo Children Home',
            'description' => 'Updated charity notice description.',
        ])->assertRedirect();
        $this->assertEquals('Updated Mabibo Charity Tour', $charity->fresh()->title);

        $election = Election::firstOrFail();
        $this->actingAs($admin)->put("/admin/elections/{$election->id}", [
            'title' => 'SONIT 2026/2027 General Elections (Updated)',
            'academic_year' => '2026/2027',
        ])->assertRedirect();
        $this->assertEquals('SONIT 2026/2027 General Elections (Updated)', $election->fresh()->title);

        // Delete verification across modules
        $this->actingAs($admin)->delete("/admin/events/{$event->id}")->assertRedirect();
        $this->assertDatabaseMissing('campus_events', ['id' => $event->id]);

        $this->actingAs($admin)->delete("/admin/sports/{$sport->id}")->assertRedirect();
        $this->assertDatabaseMissing('sports_activities', ['id' => $sport->id]);

        $this->actingAs($admin)->delete("/admin/charity/{$charity->id}")->assertRedirect();
        $this->assertDatabaseMissing('charity_campaigns', ['id' => $charity->id]);

        $lost = \App\Models\LostFoundItem::firstOrFail();
        $this->actingAs($admin)->delete("/admin/lost-found/{$lost->id}")->assertRedirect();
        $this->assertDatabaseMissing('lost_found_items', ['id' => $lost->id]);

        $ticket = HelpTicket::firstOrFail();
        $this->actingAs($admin)->delete("/admin/help-tickets/{$ticket->id}")->assertRedirect();
        $this->assertDatabaseMissing('help_tickets', ['id' => $ticket->id]);
    }
}
