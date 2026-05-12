<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Enums\UserRole;
use App\Enums\ProductType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. EMPRESA
        $company = Company::create([
            'legal_name' => 'Lava Rápido Chameleon',
            'document' => '12345678000199',
        ]);

        // 2. USUÁRIOS
        User::create([
            'company_id' => $company->id,
            'name' => 'admin',
            'password' => Hash::make('12345678'),
            'role' => UserRole::ADMIN,
        ]);

        User::create([
            'company_id' => $company->id,
            'name' => 'gerente',
            'password' => Hash::make('12345678'),
            'role' => UserRole::MANAGER,
        ]);

        User::create([
            'company_id' => $company->id,
            'name' => 'operador',
            'password' => Hash::make('12345678'),
            'role' => UserRole::OPERATOR,
        ]);

        // 3. PRODUTOS COM TABELA DE PREÇOS
        $products = [
            // LAVAGENS
            [
                'name' => 'Lavagem Completa',
                'type' => ProductType::WASH,
                'prices' => [
                    'normal' => 60.00,
                    'suv' => 70.00,
                    'pickup' => 100.00,                    
                ]
            ],
            [
                'name' => 'Lavagem Expressa',
                'type' => ProductType::WASH,
                'prices' => [
                    'normal' => 40.00,
                    'suv' => 50.00,
                    'pickup' => 60.00,                    
                ]
            ],
            [
                'name' => 'Lavagem Interna',
                'type' => ProductType::WASH,
                'prices' => [
                    'normal' => 30.00,
                    'suv' => 40.00,
                    'pickup' => 50.00,                    
                ]
            ],
            [
                'name' => 'Uber Completa',
                'type' => ProductType::WASH,
                'prices' => [
                    'normal' => 50.00,
                    'suv' => 60.00,
                    'pickup' => null,                    
                ]
            ],
            [
                'name' => 'Uber Expressa',
                'type' => ProductType::WASH,
                'prices' => [
                    'normal' => 35.00,
                    'suv' => 40.00,
                    'pickup' => null,                    
                ]
            ],
            
            // BEBIDAS (preço igual para todos os tipos)
            [
                'name' => 'Água Mineral',
                'type' => ProductType::DRINK,
                'prices' => [
                    'normal' => 3.00,
                    'suv' => 3.00,
                    'pickup' => 3.00,                    
                ]
            ],
            [
                'name' => 'Refrigerante',
                'type' => ProductType::DRINK,
                'prices' => [
                    'normal' => 5.00,
                    'suv' => 5.00,
                    'pickup' => 5.00,                    
                ]
            ],
            
            // COMIDAS
            [
                'name' => 'Salgado',
                'type' => ProductType::SNACK,
                'prices' => [
                    'normal' => 8.00,
                    'suv' => 8.00,
                    'pickup' => 8.00,                    
                ]
            ],
            [
                'name' => 'Sanduíche',
                'type' => ProductType::SNACK,
                'prices' => [
                    'normal' => 12.00,
                    'suv' => 12.00,
                    'pickup' => 12.00,
                ]
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'company_id' => $company->id,
                'name' => $productData['name'],
                'type' => $productData['type'],
                'active' => true,
            ]);

            foreach ($productData['prices'] as $vehicleType => $price) {
                if ($price !== null) {
                    ProductPrice::create([
                        'product_id' => $product->id,
                        'vehicle_type' => $vehicleType,
                        'price' => $price,
                    ]);
                }
            }
        }
    }
}