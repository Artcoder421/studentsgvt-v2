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
            '/communities',
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

        $home = $this->get('/');
        $home->assertSee('SONIT Annual Mabibo Orphanage & Welfare Charity Drive');
        $home->assertSee('images/portraits/rector.jpg');
        $home->assertSee('images/portraits/president.jpg');
        $home->assertSee('images/portraits/vice_president.jpg');
        $home->assertSee('images/campus/hero_gate.jpg');
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

        // Disabling election genuinely blocks voting (fixing legacy PHP truthiness bug!)
        $election->update(['voting_open' => false]);
        $otherStudent = User::where('reg_no', 'NIT/BIT/2023/5012')->firstOrFail();
        $this->actingAs($otherStudent)->post('/elections/vote', [
            'position' => 'President',
            'candidate_id' => $president->id,
        ])->assertSessionHas('error');
        $this->assertDatabaseCount('votes', 2);
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
}
