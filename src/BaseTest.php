<?php

namespace Fahedaljghine\ModelTest;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

abstract class BaseTest extends TestCase
{
    use DatabaseMigrations;
    use WithFaker;

    protected $user;
    protected $route;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpFaker();

        if (config('model-test.auth_user')) {
            $this->user = User::factory()->create();
        }
    }

    /**
     * @test
     * check if the user will be redirected to login page if not logged in
     */
    public function test_index_screen_will_redirect_to_login_if_no_auth()
    {
        if (config('model-test.auth_user')) {
            $view = $this->get(route($this->route . '.index'));
            $view->assertRedirect(route('login'));
        } else {
            $this->markTestSkipped('auth_user is false');
        }
    }

    /**
     * @test
     * check if the user will be redirected to 403 page if he has no permission
     */
    public function test_index_screen_will_redirect_to_403_if_no_permission()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $view = $this->actingAs($this->user)->get(route($this->route . '.index'));
            $view->assertStatus(403);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if the user will be redirected to 403 page if he has no permission
     */
    public function test_index_screen_can_be_rendered_if_user_has_permission()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $read_permission = Permission::create(['name' => 'read-' . $this->route]);
            $this->user->givePermissionTo($read_permission);
            $view = $this->actingAs($this->user)->get(route($this->route . '.index'));
            $view->assertSee($this->route);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if index page is readable if user has role which has permissions to view page
     */
    public function test_index_screen_can_be_rendered_if_user_has_role_which_has_permission()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $read_permission = Permission::create(['name' => 'read-' . $this->route]);

            $role = Role::create(['name' => 'reader']);
            $role->givePermissionTo($read_permission);

            $this->user->assignRole($role);

            $view = $this->actingAs($this->user)->get(route($this->route . '.index'));

            $view->assertSee($this->route);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if index page is readable
     */
    public function test_user_can_read_all_the_items()
    {
        $obj = $this->getFactoryObj()->create();

        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {

            $read_permission = Permission::create(['name' => 'read-' . $this->route]);

            $role = Role::create(['name' => 'reader']);
            $role->givePermissionTo($read_permission);

            $this->user->assignRole($role);

            $response = $this->actingAs($this->user)->get(route($this->route . '.index'));

        } else if (!config('model-test.laravel-permissions')) {
            $response = $this->actingAs($this->user)->get(route($this->route . '.index'));
        } else {
            $response = $this->get(route($this->route . '.index'));
        }

        $response->assertSee($obj->id);
    }

    /**
     * @test
     * check if show page is readable
     */
    public function test_user_can_read_single_item()
    {
        $obj = $this->getFactoryObj()->create();

        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $read_permission = Permission::create(['name' => 'read-' . $this->route]);

            $role = Role::create(['name' => 'reader']);
            $role->givePermissionTo($read_permission);

            $this->user->assignRole($role);

            $response = $this->actingAs($this->user)->get(route($this->route . '.show', $obj));

        } else if (!config('model-test.laravel-permissions')) {
            $response = $this->actingAs($this->user)->get(route($this->route . '.show', $obj));
        } else {
            $response = $this->get(route($this->route . '.show', $obj));
        }

        $response->assertSee("Show " . $this->route . " # " . $obj->id);
    }

    /**
     * @test
     * check if the user will be redirected to 403 page if he has no permission
     */
    public function test_create_screen_will_redirect_to_403_if_no_permission()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $view = $this->actingAs($this->user)->get(route($this->route . '.create'));

            $view->assertStatus(403);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if create page is readable
     */
    public function test_create_screen_can_be_rendered()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $create_permission = Permission::create(['name' => 'create-' . $this->route]);
            $this->user->givePermissionTo($create_permission);

            $view = $this->actingAs($this->user)->get(route($this->route . '.create'));

        } else if (!config('model-test.laravel-permissions')) {
            $view = $this->actingAs($this->user)->get(route($this->route . '.create'));
        } else {
            $view = $this->get(route($this->route . '.create'));
        }

        $view->assertSee('Create New');
    }

    /**
     * @test
     * check if store method is working properly
     */
    public function test_authenticated_users_can_create_a_new_item()
    {
        $obj = $this->getFactoryObj()->make();

        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $create_permission = Permission::create(['name' => 'create-' . $this->route]);
            $this->user->givePermissionTo($create_permission);

            $this->actingAs($this->user)
                ->post(route($this->route . '.store'), $obj->toArray())
                ->assertSessionDoesntHaveErrors();

        } else if (!config('model-test.laravel-permissions')) {

            $this->actingAs($this->user)
                ->post(route($this->route . '.store'), $obj->toArray())
                ->assertSessionDoesntHaveErrors();
        } else {
            $this->post(route($this->route . '.store'), $obj->toArray())
                ->assertSessionDoesntHaveErrors();
        }

        $db_obj = $this->getModelName()::first();
        $column = $this->getDBCheckColumns()[0];

        $this->assertEquals($obj->$column, optional($db_obj)->$column);
        $this->assertDatabaseHas($this->route, $this->getDBCheckArray($obj));
    }

    /**
     * @test
     * check if the user will be redirected to login page if not logged in
     */
    public function test_unauthenticated_users_cannot_create_a_new_item()
    {
        if (config('model-test.auth_user')) {
            $obj = $this->getFactoryObj()->make();

            $this->post(route($this->route . '.store'), $obj->toArray())
                ->assertRedirect(route('login'));
        } else {
            $this->markTestSkipped('auth_user is false');
        }
    }

    /**
     * @test
     * check if the user will be redirected to 403 page if he has no permission
     */
    public function test_unauthorized_users_cannot_create_a_new_item()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $obj = $this->getFactoryObj()->make();

            $this->actingAs($this->user)
                ->post(route($this->route . '.store'), $obj->toArray())
                ->assertStatus(403);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if the store validation is working properly
     */
    public function test_object_validation()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $create_permission = Permission::create(['name' => 'create-' . $this->route]);
            $this->user->givePermissionTo($create_permission);

            foreach ($this->getDBCheckColumns() as $checkColumn) {
                $obj = $this->getFactoryObj()->make([$checkColumn => null]);

                $this->actingAs($this->user)
                    ->post(route($this->route . '.store'), $obj->toArray())
                    ->assertSessionHasErrors($checkColumn);
            }
        } else if (!config('model-test.laravel-permissions')) {

            foreach ($this->getDBCheckColumns() as $checkColumn) {
                $obj = $this->getFactoryObj()->make([$checkColumn => null]);

                $this->actingAs($this->user)
                    ->post(route($this->route . '.store'), $obj->toArray())
                    ->assertSessionHasErrors($checkColumn);
            }
        } else {
            foreach ($this->getDBCheckColumns() as $checkColumn) {
                $obj = $this->getFactoryObj()->make([$checkColumn => null]);

                $this->post(route($this->route . '.store'), $obj->toArray())
                    ->assertSessionHasErrors($checkColumn);
            }
        }
    }

    /**
     * @test
     * check if the user will be redirected to 403 page if he has no permission
     */
    public function test_update_screen_will_redirect_to_403_if_no_permission()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $view = $this->actingAs($this->user)
                ->get(route($this->route . '.update', $this->getFactoryObj()->create()));

            $view->assertStatus(403);
        } else {
            $this->markTestSkipped('auth_user && laravel-permissions are false');
        }
    }

    /**
     * @test
     * check if create page is readable
     */
    public function test_update_screen_can_be_rendered()
    {
        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $read_permission = Permission::create(['name' => 'read-' . $this->route]);
            $update_permission = Permission::create(['name' => 'update-' . $this->route]);

            $role = Role::create(['name' => 'updater']);
            $role->givePermissionTo([$read_permission, $update_permission]);

            $this->user->assignRole($role);

            $view = $this->actingAs($this->user)
                ->get(route($this->route . '.update', $this->getFactoryObj()->create()));

        } else if (!config('model-test.laravel-permissions')) {
            $view = $this->actingAs($this->user)
                ->get(route($this->route . '.update', $this->getFactoryObj()->create()));
        } else {
            $view = $this->get(route($this->route . '.update', $this->getFactoryObj()->create()));
        }

        $view->assertSee('Update');
    }

    /**
     * @test
     * check if update method is working properly
     */
    public function test_authenticated_users_can_update_item()
    {
        $obj = $this->getFactoryObj()->create();
        $column = $this->getDBCheckColumns()[0];
        $obj->$column = $this->faker->name;

        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $update_permission = Permission::create(['name' => 'update-' . $this->route]);
            $this->user->givePermissionTo($update_permission);

            $this->actingAs($this->user)
                ->put(route($this->route . '.update', $obj->id), $obj->toArray())
                ->assertSessionDoesntHaveErrors();

        } else if (!config('model-test.laravel-permissions')) {

            $this->actingAs($this->user)
                ->put(route($this->route . '.update', $obj->id), $obj->toArray())
                ->assertSessionDoesntHaveErrors();
        } else {
            $this->put(route($this->route . '.update', $obj->id), $obj->toArray())
                ->assertSessionDoesntHaveErrors();
        }

        $db_obj = $this->getModelName()::find($obj->id);
        $this->assertEquals($obj->$column, $db_obj->$column);
    }

    /**
     * @test
     * check if delete method is working properly
     */
    public function test_authenticated_users_can_delete_item()
    {
        $obj = $this->getFactoryObj()->create();

        if (config('model-test.auth_user') && config('model-test.laravel-permissions')) {
            $update_permission = Permission::create(['name' => 'delete-' . $this->route]);
            $this->user->givePermissionTo($update_permission);

            $this->actingAs($this->user)
                ->delete(route($this->route . '.destroy', $obj->id))
                ->assertSessionDoesntHaveErrors();

        } else if (!config('model-test.laravel-permissions')) {

            $this->actingAs($this->user)
                ->delete(route($this->route . '.destroy', $obj->id))
                ->assertSessionDoesntHaveErrors();
        } else {
            $this->delete(route($this->route . '.destroy', $obj->id))
                ->assertSessionDoesntHaveErrors();
        }

        $this->assertDatabaseMissing($this->route, $this->getDBCheckArray($obj));
    }

    protected abstract function getFactoryObj();

    protected abstract function getDBCheckColumns();

    protected abstract function getModelName();

    private function getDBCheckArray($obj): array
    {
        $checks = $this->getDBCheckColumns();
        $data = [];
        foreach ($checks as $check) {
            $data[$check] = $obj->$check;
        }

        return $data;
    }
}
