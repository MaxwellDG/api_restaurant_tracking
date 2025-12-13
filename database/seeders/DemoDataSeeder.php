<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Fee;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user first (without company)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        // Create company (this will auto-create a Tax fee via Company boot method)
        $company = Company::create([
            'name' => 'Demo Coffee Shop',
            'user_id' => $admin->id,
        ]);

        // Associate admin with company
        $admin->createCompany($company);

        // Update the auto-created Tax fee to 8.5%
        Fee::where('company_id', $company->id)
            ->where('name', 'Tax')
            ->update(['value' => 8.5]);

        $fee = Fee::where('company_id', $company->id)->first();

        // Create additional users
        $users = collect([$admin]);
        $userNames = ['Alice Johnson', 'Bob Smith', 'Carol Davis', 'David Wilson', 'Emma Brown'];
        
        foreach ($userNames as $index => $name) {
            $user = User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@demo.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $user->joinCompany($company);
            $users->push($user);
        }

        // Create categories
        $categoryNames = ['Hot Drinks', 'Cold Drinks', 'Pastries', 'Sandwiches', 'Snacks'];
        $categories = collect();

        foreach ($categoryNames as $name) {
            $categories->push(Category::create([
                'name' => $name,
                'company_id' => $company->id,
            ]));
        }

        // Create items for each category
        $itemsData = [
            'Hot Drinks' => [
                ['name' => 'Espresso', 'price' => 3.50, 'description' => 'Strong Italian coffee'],
                ['name' => 'Americano', 'price' => 4.00, 'description' => 'Espresso with hot water'],
                ['name' => 'Cappuccino', 'price' => 4.50, 'description' => 'Espresso with steamed milk foam'],
                ['name' => 'Latte', 'price' => 5.00, 'description' => 'Espresso with steamed milk'],
                ['name' => 'Hot Chocolate', 'price' => 4.00, 'description' => 'Rich chocolate drink'],
            ],
            'Cold Drinks' => [
                ['name' => 'Iced Coffee', 'price' => 4.50, 'description' => 'Chilled coffee over ice'],
                ['name' => 'Iced Latte', 'price' => 5.50, 'description' => 'Espresso and milk over ice'],
                ['name' => 'Lemonade', 'price' => 3.50, 'description' => 'Fresh squeezed lemonade'],
                ['name' => 'Smoothie', 'price' => 6.00, 'description' => 'Blended fruit smoothie'],
            ],
            'Pastries' => [
                ['name' => 'Croissant', 'price' => 3.50, 'description' => 'Buttery French pastry'],
                ['name' => 'Blueberry Muffin', 'price' => 3.00, 'description' => 'Fresh baked muffin'],
                ['name' => 'Chocolate Chip Cookie', 'price' => 2.50, 'description' => 'Classic cookie'],
                ['name' => 'Cinnamon Roll', 'price' => 4.00, 'description' => 'Warm cinnamon pastry'],
            ],
            'Sandwiches' => [
                ['name' => 'Turkey Club', 'price' => 9.00, 'description' => 'Turkey, bacon, lettuce, tomato'],
                ['name' => 'BLT', 'price' => 8.00, 'description' => 'Bacon, lettuce, tomato'],
                ['name' => 'Grilled Cheese', 'price' => 6.50, 'description' => 'Classic grilled cheese'],
                ['name' => 'Veggie Wrap', 'price' => 8.50, 'description' => 'Fresh vegetables in a wrap'],
            ],
            'Snacks' => [
                ['name' => 'Chips', 'price' => 2.00, 'description' => 'Potato chips'],
                ['name' => 'Granola Bar', 'price' => 2.50, 'description' => 'Healthy oat bar'],
                ['name' => 'Mixed Nuts', 'price' => 3.00, 'description' => 'Assorted nuts'],
            ],
        ];

        $items = collect();

        foreach ($categories as $category) {
            if (isset($itemsData[$category->name])) {
                foreach ($itemsData[$category->name] as $itemData) {
                    $items->push(Item::create([
                        'name' => $itemData['name'],
                        'description' => $itemData['description'],
                        'price' => $itemData['price'],
                        'category_id' => $category->id,
                        'company_id' => $company->id,
                        'quantity' => rand(20, 100),
                        'type_of_unit' => 'each',
                    ]));
                }
            }
        }

        // Create orders
        $statuses = ['open', 'completed', 'completed', 'completed']; // More completed than open

        for ($i = 0; $i < 25; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $orderUuid = (string) Str::uuid();

            // Pick 1-4 random items for this order
            $orderItems = $items->random(rand(1, 4));
            
            $subtotal = 0;
            $orderItemsData = [];

            foreach ($orderItems as $item) {
                $quantity = rand(1, 3);
                $unitPrice = $item->price;
                $subtotal += $unitPrice * $quantity;

                $orderItemsData[] = [
                    'item_id' => $item->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                ];
            }

            // Calculate tax
            $taxAmount = round($subtotal * $fee->value / 100, 2);
            $total = $subtotal + $taxAmount;

            // Create order
            $order = new Order();
            $order->uuid = $orderUuid;
            $order->user_id = $user->id;
            $order->company_id = $company->id;
            $order->status = $status;
            $order->subtotal = $subtotal;
            $order->total = $total;
            
            if ($status === 'completed') {
                $order->receipt_id = 'RCP-' . strtoupper(Str::random(8));
                $order->completed_at = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            }
            
            $order->created_at = now()->subDays(rand(0, 30))->subHours(rand(0, 23));
            $order->updated_at = $order->created_at;
            $order->save();

            // Attach items to order (orders_items pivot)
            foreach ($orderItemsData as $itemData) {
                $order->items()->attach($itemData['item_id'], [
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
            }

            // Attach fee to order (orders_fees pivot)
            $order->fees()->attach($fee->id, [
                'value' => $taxAmount,
            ]);
        }

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Admin login: admin@demo.com / password');
    }
}

