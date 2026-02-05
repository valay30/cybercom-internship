<section class="products-display products-section">
    <div class="sort-bar">
        <!-- Quick Search -->
        <form action="plp" method="GET" class="quick-search-form">
            <!-- Preserve existing filters -->
            <?php
            foreach ($_GET as $key => $val) {
                if ($key == 'search')
                    continue;
                if (is_array($val)) {
                    foreach ($val as $v) {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                    }
                } else {
                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
                }
            }
            ?>
            <div class="search-input-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" placeholder="Search products..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if ($searchQuery): ?>
                    <a href="plp" class="clear-search" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Product Count -->
        <span class="product-count">Showing
            <strong><?php echo $offset + 1; ?>-<?php echo min($offset + count($paginatedProducts), $totalItems); ?></strong>
            of <strong><?php echo $totalItems; ?></strong> items</span>

        <!-- Sort Dropdown -->
        <form id="sortForm" action="plp" method="GET"
            style="box-shadow:none; padding:0; border:none; margin:0; background:none; max-width: 250px;">
            <!-- Preserve other filters -->
            <?php
            foreach ($_GET as $key => $val) {
                if ($key == 'sort' || $key == 'page')
                    continue;
                if (is_array($val)) {
                    foreach ($val as $v) {
                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($v) . '">';
                    }
                } else {
                    echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($val) . '">';
                }
            }
            ?>
            <select name="sort">
                <option value="">Sort By: Relevance</option>
                <option value="price_asc" <?php echo $sortOption === 'price_asc' ? 'selected' : ''; ?>>Price:
                    Low to High</option>
                <option value="price_desc" <?php echo $sortOption === 'price_desc' ? 'selected' : ''; ?>>
                    Price: High to Low</option>
            </select>
        </form>
    </div>

    <div class="product-grid">
        <?php if (empty($paginatedProducts)): ?>
            <div style="grid-column:1/-1; text-align:center; padding:50px;">
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        <?php else: ?>
            <?php foreach ($paginatedProducts as $product): ?>
                <div class="product-card">
                    <img src="<?php echo $product['image']; ?>" alt="">
                    <h3><?php echo $product['name']; ?></h3>
                    <p>₹<?php echo number_format($product['price']); ?></p>
                    <!-- Shipping Type Badge -->
                    <?php if ($product['shipping_type'] === 'express'): ?>
                        <span
                            style="display: inline-block; padding: 4px 8px; background: #28a745; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;">
                            Express
                        </span>
                    <?php else: ?>
                        <span
                            style="display: inline-block; padding: 4px 8px; background: #6c757d; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;">
                            Freight
                        </span>
                    <?php endif; ?>
                    <div class="product-actions"
                        style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                        <a href="pdp?id=<?php echo $product['id']; ?>"><button class="product-btn">View
                                Details</button></a>
                        <?php
                        $in_wishlist = in_array($product['id'], $wishlistIds);
                        ?>
                        <button class="wishlist-btn" onclick="toggleWishlist('<?php echo $product['id']; ?>', this)"
                            style="background: white; border: 1px solid #ddd; padding: 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                            <i class="<?php echo $in_wishlist ? 'fa-solid' : 'fa-regular'; ?> fa-heart"
                                style="color: <?php echo $in_wishlist ? '#ef4444' : '#64748b'; ?>; font-size: 1.2rem;"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination" style="display:flex; justify-content:center; gap:10px; margin-top:40px;">
            <?php
            $queryParams = $_GET;
            ?>

            <?php if ($currentPage > 1): ?>
                <?php $queryParams['page'] = $currentPage - 1; ?>
                <a href="?<?php echo http_build_query($queryParams); ?>" class="pagination-btn"><i
                        class="fa-solid fa-angle-left"></i> </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php $queryParams['page'] = $i; ?>
                <a href="?<?php echo http_build_query($queryParams); ?>"
                    class="pagination-btn <?php echo $i === $currentPage ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <?php $queryParams['page'] = $currentPage + 1; ?>
                <a href="?<?php echo http_build_query($queryParams); ?>" class="pagination-btn"><i
                        class="fa-solid fa-angle-right"></i></a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>