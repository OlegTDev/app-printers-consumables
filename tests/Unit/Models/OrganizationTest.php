<?php

namespace Tests\Unit\Models;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;


    /** @var Organization[] */
    private array $organizations = [];


    public function setUp(): void
    {
        parent::setUp();


        $this->organizations = Organization::factory()->createMany([
            ['code' => 'x0000', 'name' => 'Parent Org 0000'],
            ['code' => 'x0001', 'name' => 'Child Org 0001', 'parent' => 'x0000'],
            ['code' => 'x0003', 'name' => 'Parent Org 0003'],
        ])->keyBy('code')->all();
    }

    public function test_it_can_has_parent_organization(): void
    {
        $child = $this->organizations['x0001'];
        $parent = $this->organizations['x0000'];

        $this->assertTrue($child->parentOrganization->is($parent));
    }

    public function test_it_can_has_child_organizations(): void
    {
        $child = $this->organizations['x0001'];
        $parent = $this->organizations['x0000'];

        $this->assertTrue($parent->childOrganizations->contains($child));
    }

    public function test_it_scope_filter(): void
    {
        // 1 - text parent
        $organizations1 = Organization::query()->filter(['search' => 'parent'])->get();
        $this->assertCount(2, $organizations1);

        $this->assertTrue($organizations1->doesntContain($this->organizations['x0001']));

        $this->assertTrue($organizations1->contains($this->organizations['x0000']));
        $this->assertTrue($organizations1->contains($this->organizations['x0003']));

        // 2 - code is x0001
        $organizations2 = Organization::query()->filter(['search' => 'x0001'])->get();
        $this->assertCount(1, $organizations2);

        $this->assertTrue($organizations2->contains($this->organizations['x0001']));

        $this->assertTrue($organizations2->doesntContain($this->organizations['x0000']));
        $this->assertTrue($organizations2->doesntContain($this->organizations['x0003']));
    }


    public function test_it_has_many_users(): void
    {
        $user1 = User::factory()->create([
            'name' => 'test_user_0001',
            'email' => 'test_user_0001@test.ru',
            'org_code' => '',
        ]);
        $user2 = User::factory()->create([
            'name' => 'test_user_0002',
            'email' => 'test_user_0002@test.ru',
            'org_code' => '',
        ]);

        // Associate
        $orgParent001 = $this->organizations['x0000'];
        $orgParent001->users()->attach([$user1->id]);

        // Tests
        $orgParent001->load('users');

        $this->assertTrue($orgParent001->users->contains($user1));
        $this->assertTrue($orgParent001->users->doesntContain($user2));

        $this->assertCount(1, $orgParent001->users);
    }

}

