<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Default categories for new users.
     */
    protected array $defaultCategories = [
        // Income categories
        'income' => [
            ['name' => 'Salario', 'icon' => 'briefcase', 'color' => '#22c55e'],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#10b981'],
            ['name' => 'Inversiones', 'icon' => 'trending-up', 'color' => '#14b8a6'],
            ['name' => 'Ventas', 'icon' => 'shopping-bag', 'color' => '#06b6d4'],
            ['name' => 'Regalos', 'icon' => 'gift', 'color' => '#0ea5e9'],
            ['name' => 'Reembolsos', 'icon' => 'rotate-ccw', 'color' => '#3b82f6'],
            ['name' => 'Otros Ingresos', 'icon' => 'plus-circle', 'color' => '#6366f1'],
        ],
        // Expense categories
        'expense' => [
            ['name' => 'Alimentación', 'icon' => 'utensils', 'color' => '#ef4444', 'children' => [
                ['name' => 'Supermercado', 'icon' => 'shopping-cart', 'color' => '#f87171'],
                ['name' => 'Restaurantes', 'icon' => 'coffee', 'color' => '#fca5a5'],
                ['name' => 'Delivery', 'icon' => 'bike', 'color' => '#fecaca'],
            ]],
            ['name' => 'Transporte', 'icon' => 'car', 'color' => '#f97316', 'children' => [
                ['name' => 'Gasolina', 'icon' => 'fuel', 'color' => '#fb923c'],
                ['name' => 'Uber/Taxi', 'icon' => 'navigation', 'color' => '#fdba74'],
                ['name' => 'Transporte Público', 'icon' => 'bus', 'color' => '#fed7aa'],
                ['name' => 'Estacionamiento', 'icon' => 'square', 'color' => '#ffedd5'],
            ]],
            ['name' => 'Hogar', 'icon' => 'home', 'color' => '#eab308', 'children' => [
                ['name' => 'Renta/Hipoteca', 'icon' => 'key', 'color' => '#facc15'],
                ['name' => 'Electricidad', 'icon' => 'zap', 'color' => '#fde047'],
                ['name' => 'Agua', 'icon' => 'droplet', 'color' => '#fef08a'],
                ['name' => 'Gas', 'icon' => 'flame', 'color' => '#fef9c3'],
                ['name' => 'Internet', 'icon' => 'wifi', 'color' => '#fefce8'],
                ['name' => 'Mantenimiento', 'icon' => 'wrench', 'color' => '#fef3c7'],
            ]],
            ['name' => 'Salud', 'icon' => 'heart', 'color' => '#ec4899', 'children' => [
                ['name' => 'Médico', 'icon' => 'stethoscope', 'color' => '#f472b6'],
                ['name' => 'Farmacia', 'icon' => 'pill', 'color' => '#f9a8d4'],
                ['name' => 'Gym', 'icon' => 'dumbbell', 'color' => '#fbcfe8'],
            ]],
            ['name' => 'Entretenimiento', 'icon' => 'film', 'color' => '#8b5cf6', 'children' => [
                ['name' => 'Streaming', 'icon' => 'tv', 'color' => '#a78bfa'],
                ['name' => 'Cine', 'icon' => 'popcorn', 'color' => '#c4b5fd'],
                ['name' => 'Videojuegos', 'icon' => 'gamepad', 'color' => '#ddd6fe'],
                ['name' => 'Salidas', 'icon' => 'users', 'color' => '#ede9fe'],
            ]],
            ['name' => 'Compras', 'icon' => 'shopping-bag', 'color' => '#06b6d4', 'children' => [
                ['name' => 'Ropa', 'icon' => 'shirt', 'color' => '#22d3ee'],
                ['name' => 'Electrónica', 'icon' => 'smartphone', 'color' => '#67e8f9'],
                ['name' => 'Hogar', 'icon' => 'sofa', 'color' => '#a5f3fc'],
            ]],
            ['name' => 'Educación', 'icon' => 'graduation-cap', 'color' => '#3b82f6', 'children' => [
                ['name' => 'Cursos', 'icon' => 'book-open', 'color' => '#60a5fa'],
                ['name' => 'Libros', 'icon' => 'book', 'color' => '#93c5fd'],
                ['name' => 'Software', 'icon' => 'code', 'color' => '#bfdbfe'],
            ]],
            ['name' => 'Mascotas', 'icon' => 'paw-print', 'color' => '#84cc16'],
            ['name' => 'Seguros', 'icon' => 'shield', 'color' => '#64748b'],
            ['name' => 'Impuestos', 'icon' => 'landmark', 'color' => '#475569'],
            ['name' => 'Deudas', 'icon' => 'credit-card', 'color' => '#dc2626'],
            ['name' => 'Otros Gastos', 'icon' => 'more-horizontal', 'color' => '#9ca3af'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories for all existing users
        User::all()->each(function (User $user) {
            $this->createCategoriesForUser($user);
        });
    }

    /**
     * Create default categories for a specific user.
     */
    public function createCategoriesForUser(User $user): void
    {
        $sortOrder = 0;

        foreach ($this->defaultCategories as $type => $categories) {
            foreach ($categories as $categoryData) {
                $children = $categoryData['children'] ?? [];
                unset($categoryData['children']);

                $category = Category::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'sort_order' => $sortOrder++,
                    ...$categoryData,
                ]);

                // Create subcategories
                foreach ($children as $childData) {
                    Category::create([
                        'user_id' => $user->id,
                        'parent_id' => $category->id,
                        'type' => $type,
                        'sort_order' => $sortOrder++,
                        ...$childData,
                    ]);
                }
            }
        }
    }
}
