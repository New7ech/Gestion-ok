<?php

namespace Tests\Feature;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function creation_permission_normalise_le_nom(): void
    {
        $response = $this->post(route('permissions.store'), [
            'name' => '  Utilisateurs Creer__Nouveau  ',
        ]);

        $response->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'name' => 'utilisateurs-creer-nouveau',
            'guard_name' => 'web',
        ]);
    }

    #[Test]
    public function creation_permission_refuse_les_doublons_sur_guard_web(): void
    {
        Permission::query()->create([
            'name' => 'users-delete',
            'guard_name' => 'web',
        ]);

        $response = $this->from(route('permissions.create'))
            ->post(route('permissions.store'), [
                'name' => ' USERS_DELETE ',
            ]);

        $response->assertSessionHasErrors('name');

        $this->assertSame(1, Permission::query()->count());
    }

    #[Test]
    public function mise_a_jour_permission_accepte_le_meme_nom_normalise(): void
    {
        $permission = Permission::query()->create([
            'name' => 'articles.read',
            'guard_name' => 'web',
        ]);

        $response = $this->put(route('permissions.update', $permission), [
            'name' => '  ARTICLES.READ  ',
        ]);

        $response->assertRedirect(route('permissions.index'));

        $this->assertDatabaseHas('permissions', [
            'id' => $permission->id,
            'name' => 'articles.read',
            'guard_name' => 'web',
        ]);
    }
}
