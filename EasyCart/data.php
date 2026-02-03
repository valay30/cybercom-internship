<!-- <?php
// Static Data for EasyCart
$categories = [
    ['id' => 'electronics', 'name' => 'Electronics', 'icon' => 'fa-solid fa-laptop'],
    ['id' => 'fashion', 'name' => 'Fashion', 'icon' => 'fa-solid fa-shirt'],
    ['id' => 'home', 'name' => 'Home Appliances', 'icon' => 'fa-solid fa-house'],
    ['id' => 'beauty', 'name' => 'Beauty', 'icon' => 'fa-solid fa-spa']
];

$brands = [
    ['id' => 'apple', 'name' => 'Apple', 'icon' => 'fa-brands fa-apple'],
    ['id' => 'samsung', 'name' => 'Samsung', 'icon' => 'fa-solid fa-mobile-screen'],
    ['id' => 'nike', 'name' => 'Nike', 'icon' => 'fa-solid fa-shoe-prints'],
    ['id' => 'adidas', 'name' => 'Adidas', 'icon' => 'fa-solid fa-futbol']
];

$products = [
    'p1' => [
        'id' => 'p1',
        'name' => 'Wireless Headphones',
        'price' => 9999,
        'image' => 'images/Wireless Headphones.png',
        'images' => [
            'images/Wireless Headphones.png',
            'images/Wireless Headphones_2.png',
            'images/Wireless Headphones.png'
        ],
        'category' => 'electronics',
        'brand' => 'apple',
        'description' => 'Experience premium sound quality with our Wireless Headphones. Featuring advanced noise-cancellation technology, these headphones deliver crystal-clear audio.',
        'features' => ['Active Noise Cancellation', '30 Hours Battery Life', 'Bluetooth 5.0'],
        'shipping_type' => 'freight'
    ],
    'p2' => [
        'id' => 'p2',
        'name' => 'Smart Watch',
        'price' => 100,
        'image' => 'images/Smart Watch.png',
        'images' => [
            'images/Smart Watch.png',
            'images/Smart Watch_2.png',
            'images/Smart Watch.png'
        ],
        'category' => 'electronics',
        'brand' => 'apple',
        'description' => 'Stay connected and track your fitness goals with this stylish Smart Watch.',
        'features' => ['Heart Rate Monitor', 'Sleep Tracking', 'Water Resistant'],
        'shipping_type' => 'express'
    ],
    'p3' => [
        'id' => 'p3',
        'name' => 'Running Shoes',
        'price' => 999,
        'image' => 'images/Running Shoes.png',
        'images' => [
            'images/Running Shoes.png',
            'images/Running Shoes_2.png',
            'images/Running Shoes.png'
        ],
        'category' => 'fashion',
        'brand' => 'nike',
        'description' => 'Lightweight and breathable running shoes designed for ultimate comfort.',
        'features' => ['Breathable Mesh', 'Cushioned Sole', 'Durable Grip'],
        'shipping_type' => 'freight'
    ],
    'p4' => [
        'id' => 'p4',
        'name' => 'Laptop Bag',
        'price' => 250,
        'image' => 'images/Laptop Bag.png',
        'images' => [
            'images/Laptop Bag.png',
            'images/Laptop Bag.png',
            'images/Laptop Bag.png'
        ],
        'category' => 'fashion',
        'brand' => 'adidas',
        'description' => 'Sleek and professional laptop bag with multiple compartments for organization.',
        'features' => ['Waterproof Material', 'Padded Sleeve', 'Adjustable Straps'],
        'shipping_type' => 'express'
    ],
    'p5' => [
        'id' => 'p5',
        'name' => 'Bluetooth Speaker',
        'price' => 1999,
        'image' => 'images/Bluetooth Speaker.png',
        'images' => [
            'images/Bluetooth Speaker.png',
            'images/Bluetooth Speaker.png',
            'images/Bluetooth Speaker.png'
        ],
        'category' => 'electronics',
        'brand' => 'samsung',
        'description' => 'Compact and powerful, this Bluetooth speaker delivers rich audio and deep bass for an immersive listening experience.',
        'features' => ['Portable Design', '12 Hours Playtime', 'Deep Bass'],
        'shipping_type' => 'freight'
    ],
    'p6' => [
        'id' => 'p6',
        'name' => 'Gaming Mouse',
        'price' => 280,
        'image' => 'images/Gaming Mouse.png',
        'images' => [
            'images/Gaming Mouse.png',
            'images/Gaming Mouse.png',
            'images/Gaming Mouse.png'
        ],
        'category' => 'electronics',
        'brand' => 'logitech',
        'description' => 'High-precision gaming mouse with customizable DPI settings and ergonomic design for long gaming sessions.',
        'features' => ['Customizable DPI', 'Ergonomic Design', 'RGB Lighting'],
        'shipping_type' => 'express'
    ],
    'p7' => [
        'id' => 'p7',
        'name' => 'Mechanical Keyboard',
        'price' => 1299,
        'image' => 'images/Mechanical Keyboard.png',
        'images' => [
            'images/Mechanical Keyboard.png',
            'images/Mechanical Keyboard.png',
            'images/Mechanical Keyboard.png'
        ],
        'category' => 'electronics',
        'brand' => 'asus',
        'description' => 'Durable mechanical keyboard with tactile switches and customizable RGB backlighting for an enhanced typing experience.',
        'features' => ['Breathable Mesh', 'Cushioned Sole', 'Durable Grip'],
        'shipping_type' => 'freight'
    ],
    'p8' => [
        'id' => 'p8',
        'name' => 'USB-C Hub',
        'price' => 200,
        'image' => 'images/USB-C Hub.png',
        'images' => [
            'images/USB-C Hub.png',
            'images/USB-C Hub.png',
            'images/USB-C Hub.png'
        ],
        'category' => 'electronics',
        'brand' => 'trip',
        'description' => 'Expand your connectivity with this versatile USB-C hub, featuring multiple ports for high-speed data transfer and 4K HDMI output.',
        'features' => ['4K HDMI Output', 'High-Speed Data Transfer', 'Multiple USB Ports'],
        'shipping_type' => 'express'
    ],
    'p9' => [
        'id' => 'p9',
        'name' => 'Webcam HD',
        'price' => 999,
        'image' => 'images/Webcam HD.png',
        'images' => [
            'images/Webcam HD.png',
            'images/Webcam HD.png',
            'images/Webcam HD.png'
        ],
        'category' => 'electronics',
        'brand' => 'logitech',
        'description' => 'High-definition webcam with a wide-angle lens and built-in microphone for clear video calls and streaming.',
        'features' => ['1080p Full HD', 'Wide-Angle Lens', 'Built-in Microphone'],
        'shipping_type' => 'freight'
    ],
    'p10' => [
        'id' => 'p10',
        'name' => 'Designer Sunglasses',
        'price' => 100,
        'image' => 'images/Designer Sunglasses.png',
        'images' => [
            'images/Designer Sunglasses.png',
            'images/Designer Sunglasses_2.png',
            'images/Designer Sunglasses.png'
        ],
        'category' => 'fashion',
        'brand' => 'rayban',
        'description' => 'Stylish and protective sunglasses with UV400 lenses. Perfect for any sunny day.',
        'features' => ['UV Protection', 'Scratch Resistant', 'Lightweight Frame'],
        'shipping_type' => 'express'
    ],
    'p11' => [
        'id' => 'p11',
        'name' => 'Coffee Maker',
        'price' => 3999,
        'image' => 'images/Coffee Maker.png',
        'images' => [
            'images/Coffee Maker.png',
            'images/Coffee Maker.png',
            'images/Coffee Maker.png'
        ],
        'category' => 'home',
        'brand' => 'philips',
        'description' => 'Brew cafe-quality coffee at home with this easy-to-use drip coffee maker. Features a programmable timer.',
        'features' => ['Programmable Timer', 'Keep Warm Function', 'Reusable Filter'],
        'shipping_type' => 'freight'
    ],
    'p12' => [
        'id' => 'p12',
        'name' => 'Face Serum',
        'price' => 150,
        'image' => 'images/Face Serum.png',
        'images' => [
            'images/Face Serum.png',
            'images/Face Serum.png',
            'images/Face Serum.png'
        ],
        'category' => 'beauty',
        'brand' => 'loreal',
        'description' => 'Revitalize your skin with this hydrating face serum. Rich in Vitamin C and Hyaluronic Acid.',
        'features' => ['Hydrating', 'Vitamin C Enriched', 'For All Skin Types'],
        'shipping_type' => 'express'
    ],
    'p13' => [
        'id' => 'p13',
        'name' => 'Gaming Headset',
        'price' => 2500,
        'image' => 'images/Gaming Headset.png',
        'images' => [
            'images/Gaming Headset.png',
            'images/Gaming Headset.png',
            'images/Gaming Headset.png'
        ],
        'category' => 'electronics',
        'brand' => 'logitech',
        'description' => 'Immersive surround sound gaming headset with a noise-cancelling microphone for clear communication.',
        'features' => ['7.1 Surround Sound', 'Noise Cancelling Mic', 'Memory Foam Earcups'],
        'shipping_type' => 'freight'
    ],
    'p14' => [
        'id' => 'p14',
        'name' => 'Cotton T-Shirt',
        'price' => 289,
        'image' => 'images/Cotton T-Shirt.png',
        'images' => [
            'images/Cotton T-Shirt.png',
            'images/Cotton T-Shirt.png',
            'images/Cotton T-Shirt.png'
        ],
        'category' => 'fashion',
        'brand' => 'nike',
        'description' => 'Soft and breathable 100% cotton t-shirt. Essential casual wear for everyday comfort.',
        'features' => ['100% Cotton', 'Regular Fit', 'Machine Washable'],
        'shipping_type' => 'express'
    ],
    'p15' => [
        'id' => 'p15',
        'name' => 'Air Fryer',
        'price' => 5999,
        'image' => 'images/Air Fryer.png',
        'images' => [
            'images/Air Fryer.png',
            'images/Air Fryer.png',
            'images/Air Fryer.png'
        ],
        'category' => 'home',
        'brand' => 'philips',
        'description' => 'Cook healthy and delicious meals with up to 90% less fat. Versatile cooking functions for frying, baking, and roasting.',
        'features' => ['Rapid Air Technology', 'Digital Touchscreen', 'Easy to Clean'],
        'shipping_type' => 'freight'
    ],
    'p16' => [
        'id' => 'p16',
        'name' => 'Matte Lipstick',
        'price' => 200,
        'image' => 'images/Matte Lipstick.png',
        'images' => [
            'images/Matte Lipstick.png',
            'images/Matte Lipstick.png',
            'images/Matte Lipstick.png'
        ],
        'category' => 'beauty',
        'brand' => 'lakme',
        'description' => 'Long-lasting matte lipstick with intense color payoff. Enriched with moisturizing ingredients to keep lips soft.',
        'features' => ['Long Lasting', 'Intense Color', 'Moisturizing'],
        'shipping_type' => 'express'
    ],
    'p17' => [
        'id' => 'p17',
        'name' => 'Yoga Mat',
        'price' => 299,
        'image' => 'images/Yoga Mat.png',
        'images' => [
            'images/Yoga Mat.png',
            'images/Yoga Mat.png',
            'images/Yoga Mat.png'
        ],
        'category' => 'fashion',
        'brand' => 'adidas',
        'description' => 'Non-slip yoga mat providing excellent cushioning and support for your practice. Lightweight and portable.',
        'features' => ['Non-Slip Surface', 'Eco-Friendly Material', 'Carrying Strap Included'],
        'shipping_type' => 'express'
    ],
    'p18' => [
        'id' => 'p18',
        'name' => 'Portable Power Bank',
        'price' => 1299,
        'image' => 'images/Power Bank.png',
        'images' => [
            'images/Power Bank.png',
            'images/Power Bank.png',
            'images/Power Bank.png'
        ],
        'category' => 'electronics',
        'brand' => 'samsung',
        'description' => 'High-capacity 10000mAh power bank with fast charging support. Keep your devices charged on the go.',
        'features' => ['10000mAh Capacity', 'Fast Charging', 'Dual USB Output'],
        'shipping_type' => 'freight'
    ],
];

// Coupon Codes
$coupons = [
    'SAVE5' => ['discount' => 5, 'description' => '5% off on total'],
    'SAVE10' => ['discount' => 10, 'description' => '10% off on total'],
    'SAVE15' => ['discount' => 15, 'description' => '15% off on total'],
    'SAVE20' => ['discount' => 20, 'description' => '20% off on total']
];

$orders = [
    [
        'id' => 'ORD-001',
        'date' => 'January 10, 2026',
        'status' => 'delivered',
        'total' => 11998,
        'items' => [
            ['name' => 'Wireless Headphones', 'image' => 'images/Wireless Headphones.png'],
            ['name' => 'Smart Watch', 'image' => 'images/Smart Watch.png']
        ]
    ],
    [
        'id' => 'ORD-002',
        'date' => 'January 15, 2026',
        'status' => 'delivered',
        'total' => 999,
        'items' => [
            ['name' => 'Running Shoes', 'image' => 'images/Running Shoes.png']
        ]
    ],
    [
        'id' => 'ORD-003',
        'date' => 'January 20, 2026',
        'status' => 'delivered',
        'total' => 1500,
        'items' => [
            ['name' => 'USB-C Hub', 'image' => 'images/USB-C Hub.png'],
            ['name' => 'Webcam HD', 'image' => 'images/Webcam HD.png']
        ]
    ]
];
?> -->