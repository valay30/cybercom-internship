<?php
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
        'features' => ['Active Noise Cancellation', '30 Hours Battery Life', 'Bluetooth 5.0']
    ],
    'p2' => [
        'id' => 'p2',
        'name' => 'Smart Watch',
        'price' => 1999,
        'image' => 'images/Smart Watch.png',
        'images' => [
            'images/Smart Watch.png',
            'images/Smart Watch_2.png',
            'images/Smart Watch.png'
        ],
        'category' => 'electronics',
        'brand' => 'apple',
        'description' => 'Stay connected and track your fitness goals with this stylish Smart Watch.',
        'features' => ['Heart Rate Monitor', 'Sleep Tracking', 'Water Resistant']
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
        'features' => ['Breathable Mesh', 'Cushioned Sole', 'Durable Grip']
    ],
    'p4' => [
        'id' => 'p4',
        'name' => 'Laptop Bag',
        'price' => 4999,
        'image' => 'images/Laptop Bag.png',
        'images' => [
            'images/Laptop Bag.png',
            'images/Laptop Bag.png',
            'images/Laptop Bag.png'
        ],
        'category' => 'fashion',
        'brand' => 'adidas',
        'description' => 'Sleek and professional laptop bag with multiple compartments for organization.',
        'features' => ['Waterproof Material', 'Padded Sleeve', 'Adjustable Straps']
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
        'features' => ['Portable Design', '12 Hours Playtime', 'Deep Bass']
    ],
    'p6' => [
        'id' => 'p6',
        'name' => 'Gaming Mouse',
        'price' => 999,
        'image' => 'images/Gaming Mouse.png',
        'images' => [
            'images/Gaming Mouse.png',
            'images/Gaming Mouse.png',
            'images/Gaming Mouse.png'
        ],
        'category' => 'electronics',
        'brand' => 'logitech',
        'description' => 'High-precision gaming mouse with customizable DPI settings and ergonomic design for long gaming sessions.',
        'features' => ['Customizable DPI', 'Ergonomic Design', 'RGB Lighting']
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
        'features' => ['Breathable Mesh', 'Cushioned Sole', 'Durable Grip']
    ],
    'p8' => [
        'id' => 'p8',
        'name' => 'USB-C Hub',
        'price' => 1500,
        'image' => 'images/USB-C Hub.png',
        'images' => [
            'images/USB-C Hub.png',
            'images/USB-C Hub.png',
            'images/USB-C Hub.png'
        ],
        'category' => 'electronics',
        'brand' => 'trip',
        'description' => 'Expand your connectivity with this versatile USB-C hub, featuring multiple ports for high-speed data transfer and 4K HDMI output.',
        'features' => ['4K HDMI Output', 'High-Speed Data Transfer', 'Multiple USB Ports']
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
        'features' => ['1080p Full HD', 'Wide-Angle Lens', 'Built-in Microphone']
    ],
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
?>