<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>EasyCart - Products</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include __DIR__ . '/../common/header.php'; ?>

    <main>
        <div class="plp-container">
            <!-- Sidebar Filters -->
            <?php include 'app/views/products/filters-sidebar.php'; ?>

            <!-- Product Grid -->
            <?php include 'app/views/products/product-grid.php'; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../common/footer.php'; ?>

    <script>
        const WISHLIST_IDS = <?php echo json_encode($wishlistIds); ?>;
    </script>
    <script src="js/plp.js"></script>
    <script src="js/wishlist.js"></script>
</body>

</html>