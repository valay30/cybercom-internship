<aside class="filters-sidebar">
    <h3><i class="fa-solid fa-filter"></i> Filters</h3>
    <form id="filterForm" action="plp.php" method="GET">

        <!-- Categories -->
        <div class="filter-group">
            <h4>Categories</h4>
            <div class="checkbox-list">
                <?php foreach ($categories as $cat): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="category[]" value="<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $selectedCategories) ? 'checked' : ''; ?>>
                        <?php echo $cat['name']; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Brands -->
        <div class="filter-group">
            <h4>Brands</h4>
            <div class="checkbox-list">
                <?php foreach ($brands as $brand): ?>
                    <label class="checkbox-item">
                        <input type="checkbox" name="brand[]" value="<?php echo $brand['id']; ?>" <?php echo in_array($brand['id'], $selectedBrands) ? 'checked' : ''; ?>>
                        <?php echo $brand['name']; ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price Range -->
        <div class="filter-group">
            <h4>Price Range (₹)</h4>
            <div class="price-slider-container">
                <div class="price-values">
                    <span>₹<span id="min-price-disp"><?php echo $minPrice ?: '0'; ?></span></span>
                    <span>–</span>
                    <span>₹<span
                            id="max-price-disp"><?php echo $maxPrice == 15000 ? '15000+' : ($maxPrice ?: '15000'); ?></span></span>
                </div>

                <div class="range-slider">
                    <div class="slider-track"></div>
                    <input type="range" class="min-range" min="0" max="15000"
                        value="<?php echo $minPrice ?: '0'; ?>" step="100" id="min-range-input">
                    <input type="range" class="max-range" min="0" max="15000"
                        value="<?php echo $maxPrice ?: '15000'; ?>" step="100" id="max-range-input">
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="min_price" id="hidden_min_price"
                    value="<?php echo $minPrice ?: 0; ?>">
                <input type="hidden" name="max_price" id="hidden_max_price"
                    value="<?php echo $maxPrice == 15000 ? 15000 : $maxPrice; ?>">
            </div>
        </div>

        <button type="submit" class="apply-filters-btn">Apply Filters</button>
        <?php if (!empty($_GET)): ?>
            <a href="plp.php" style="display:block; text-align:center; margin-top:10px; font-size:0.9rem;">Clear
                All</a>
        <?php endif; ?>
    </form>
</aside>