<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ImproveRolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des rôles supplémentaires pour une meilleure gestion
        $roles = [
            ['name' => 'super_admin', 'guard_name' => 'web'],
            ['name' => 'manager', 'guard_name' => 'web'],
            ['name' => 'employee', 'guard_name' => 'web'],
            ['name' => 'guest', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(['name' => $roleData['name']], ['guard_name' => $roleData['guard_name']]);
        }

        // Organiser les permissions par catégorie
        $permissionsByCategory = [
            'Utilisateurs' => [
                'show-users',
                'create-users',
                'edit-users',
                'delete-users',
                'manage-user-roles',
                'manage-user-permissions',
            ],
            'Articles' => [
                'show-articles',
                'create-articles',
                'edit-articles',
                'delete-articles',
                'manage-articles-stock',
                'publish-articles',
            ],
            'Fournisseurs' => [
                'show-fournisseurs',
                'create-fournisseurs',
                'edit-fournisseurs',
                'delete-fournisseurs',
                'validate-fournisseurs',
            ],
            'Catégories' => [
                'show-categories',
                'create-categories',
                'edit-categories',
                'delete-categories',
                'organize-categories',
            ],
            'Emplacements' => [
                'show-emplacements',
                'create-emplacements',
                'edit-emplacements',
                'delete-emplacements',
                'manage-emplacements-stock',
            ],
            'Factures' => [
                'show-factures',
                'create-factures',
                'edit-factures',
                'delete-factures',
                'validate-factures',
                'manage-facture-payments',
            ],
            'Rôles et Permissions' => [
                'show-roles',
                'create-roles',
                'edit-roles',
                'delete-roles',
                'manage-permissions',
                'assign-roles',
                'revoke-roles',
            ],
            'Rapports' => [
                'view-reports',
                'generate-reports',
                'export-reports',
                'manage-reports',
            ],
            'Système' => [
                'system-settings',
                'system-backup',
                'system-logs',
                'system-maintenance',
            ],
        ];

        // Créer toutes les permissions
        foreach ($permissionsByCategory as $category => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission], ['guard_name' => 'web']);
            }
        }

        // Assigner des permissions aux rôles selon leur niveau
        $roleAssignments = [
            'super_admin' => array_keys($permissionsByCategory), // Tous les droits
            'manager' => [
                'Utilisateurs', 'Articles', 'Fournisseurs', 'Catégories', 'Emplacements', 'Factures', 'Rapports'
            ],
            'employee' => [
                'Articles', 'Fournisseurs', 'Catégories', 'Emplacements'
            ],
            'guest' => [
                'Articles', 'Fournisseurs', 'Catégories'
            ]
        ];

        foreach ($roleAssignments as $roleName => $categories) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissions = [];
                foreach ($categories as $category) {
                    $permissions = array_merge($permissions, $permissionsByCategory[$category]);
                }
                $role->syncPermissions($permissions);
            }
        }

        $this->command->info('Rôles et permissions améliorés avec succès!');
        $this->command->info('Structure des permissions créée par catégorie');
        $this->command->info('Assignation hiérarchique des permissions configurée');
    }
}
