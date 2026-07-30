<?php

namespace Tests\Unit\Models;

use App\Models\Auth\User;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @var Organization[]
     */
    private array $organizations = [];


    public function setUp(): void
    {
        parent::setUp();


        // Organizations

        $this->organizations['parent'] = Organization::create([
            'code' => 'x0000',
            'name' => 'Parent Org 0000',
        ]);

        $this->organizations['child'] = Organization::create([
            'code' => 'x0001',
            'name' => 'Child Org 0001',
            'parent' => 'x0000',
        ]);

        $this->organizations['parent-some'] = Organization::create([
            'code' => 'x0003',
            'name' => 'Parent Org 0003',
        ]);
    }

    public function test_it_can_has_parent_organization(): void
    {
        $child = $this->organizations['child'];
        $parent = $this->organizations['parent'];

        $this->assertTrue($child->parentOrganization->is($parent));
    }

    public function test_it_can_has_child_organizations(): void
    {
        $child = $this->organizations['child'];
        $parent = $this->organizations['parent'];

        $this->assertTrue($parent->childOrganizations->contains($child));
    }

    public function test_it_scope_filter(): void
    {
        // 1 - text parent
        $organizations1 = Organization::query()->filter(['search' => 'parent'])->get();
        $this->assertTrue(2 === $organizations1->count());

        $this->assertTrue($organizations1->doesntContain($this->organizations['child']));

        $this->assertTrue($organizations1->contains($this->organizations['parent']));
        $this->assertTrue($organizations1->contains($this->organizations['parent-some']));

        // 2 - code is x0001
        $organizations2 = Organization::query()->filter(['search' => 'x0001'])->get();
        $this->assertTrue(1 === $organizations2->count());

        $this->assertTrue($organizations2->contains($this->organizations['child']));

        $this->assertTrue($organizations2->doesntContain($this->organizations['parent']));
        $this->assertTrue($organizations2->doesntContain($this->organizations['parent-some']));
    }


    public function test_it_has_many_users(): void
    {
        // Create users
        $user1 = User::create([
            'name' => 'test_user_0001',
            'email' => 'test_user_0001@test.ru',
            'org_code' => '',
        ]);
        $user2 = User::create([
            'name' => 'test_user_0002',
            'email' => 'test_user_0002@test.ru',
            'org_code' => '',
        ]);

        // Associate
        $orgParent001 = $this->organizations['parent'];
        $orgParent001->users()->attach([$user1->id]);


        // Tests
        $orgParent001->load('users');

        $this->assertTrue($orgParent001->users->contains($user1));
        $this->assertTrue($orgParent001->users->doesntContain($user2));

        $this->assertEquals(1, $orgParent001->users->count());
    }

}

