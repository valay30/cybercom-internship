
const minRange = document.getElementById('min-range-input');
const maxRange = document.getElementById('max-range-input');
const minDisp = document.getElementById('min-price-disp');
const maxDisp = document.getElementById('max-price-disp');
const hiddenMin = document.getElementById('hidden_min_price');
const hiddenMax = document.getElementById('hidden_max_price');
const track = document.querySelector('.slider-track');
const rangeMax = 15000;

function updateTrack() {
    const minPercent = (minRange.value / rangeMax) * 100;
    const maxPercent = (maxRange.value / rangeMax) * 100;
    track.style.background = `linear-gradient(to right, #ddd ${minPercent}%, #3498db ${minPercent}%, #3498db ${maxPercent}%, #ddd ${maxPercent}%)`;
}

minRange.addEventListener('input', () => {
    if (parseInt(minRange.value) > parseInt(maxRange.value) - 500) {
        minRange.value = parseInt(maxRange.value) - 500;
    }
    minDisp.textContent = parseInt(minRange.value).toLocaleString();
    hiddenMin.value = minRange.value;
    updateTrack();
});

maxRange.addEventListener('input', () => {
    if (parseInt(maxRange.value) < parseInt(minRange.value) + 500) {
        maxRange.value = parseInt(minRange.value) + 500;
    }
    maxDisp.textContent = parseInt(maxRange.value).toLocaleString() + (maxRange.value == 15000 ? '+' : '');
    hiddenMax.value = maxRange.value;
    updateTrack();
});

// Initialize
updateTrack();

// ==========================================
// AJAX FILTERING - NO PAGE RELOAD
// ==========================================

// Get form elements
const filterForm = document.getElementById('filterForm');
const sortForm = document.getElementById('sortForm');
const searchForm = document.querySelector('.quick-search-form');

// Intercept filter form submission
if (filterForm) {
    filterForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadProducts();
    });

    // Auto-apply on checkbox change
    const checkboxes = filterForm.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', () => loadProducts());
    });
}

// Intercept sort dropdown change
if (sortForm) {
    const sortSelect = sortForm.querySelector('select[name="sort"]');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => loadProducts());
    }
}

// Intercept search form
if (searchForm) {
    searchForm.addEventListener('submit', function (e) {
        e.preventDefault();
        loadProducts();
    });

    // Add Live Search (Debounced)
    const searchInput = searchForm.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function () {
            loadProducts();
        }, 500)); // 500ms delay
    }
}

// Debounce Utility Function
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        const context = this;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
    };
}

// Intercept pagination clicks (using event delegation since pagination is dynamic)
document.addEventListener('click', function (e) {
    // Check if clicked element is a pagination link
    if (e.target.closest('.pagination-btn')) {
        e.preventDefault();
        const link = e.target.closest('.pagination-btn');
        const href = link.getAttribute('href');

        // Extract page number from URL
        const urlParams = new URLSearchParams(href.split('?')[1]);
        const page = urlParams.get('page') || 1;

        loadProducts(parseInt(page));
    }
});

// Main function to load products via AJAX
function loadProducts(page = 1) {
    // Collect filter data
    const formData = new FormData(filterForm);
    const searchInput = document.querySelector('input[name="search"]');
    const sortSelect = document.querySelector('select[name="sort"]');

    // Build URL parameters
    const params = new URLSearchParams();
    params.append('ajax', 'true'); // Tell PHP this is AJAX
    params.append('page', page);

    // Add categories
    formData.getAll('category[]').forEach(cat => params.append('category[]', cat));

    // Add brands
    formData.getAll('brand[]').forEach(brand => params.append('brand[]', brand));

    // Add price range
    params.append('min_price', formData.get('min_price'));
    params.append('max_price', formData.get('max_price'));

    // Add search
    if (searchInput && searchInput.value) {
        params.append('search', searchInput.value);
    }

    // Add sort
    if (sortSelect && sortSelect.value) {
        params.append('sort', sortSelect.value);
    }

    // Show loading spinner
    const productGrid = document.querySelector('.product-grid');
    productGrid.innerHTML = `
        <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
            <i class="fa-solid fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary);"></i>
            <p>Loading products...</p>
        </div>
    `;

    // Fetch products
    fetch(`plp?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderProducts(data.products);
                updateProductCount(data.showing_from, data.showing_to, data.total_items);
                updatePagination(data.current_page, data.total_pages);

                // Update URL without reloading (exclude ajax param)
                const urlParams = new URLSearchParams(params);
                urlParams.delete('ajax');
                const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
                window.history.replaceState({}, '', newUrl);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            productGrid.innerHTML = `
                <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                    <h3>Error loading products</h3>
                    <p>Please try again.</p>
                </div>
            `;
        });
}

// Render products in grid
function renderProducts(products) {
    const productGrid = document.querySelector('.product-grid');

    if (products.length === 0) {
        productGrid.innerHTML = `
            <div style="grid-column:1/-1; text-align:center; padding:50px;">
                <h3>No products found</h3>
                <p>Try adjusting your search or filters.</p>
            </div>
        `;
        return;
    }

    productGrid.innerHTML = products.map(product => `
        <div class="product-card">
            <img src="${product.image}" alt="${product.name}">
            <h3>${product.name}</h3>
            <p>₹${Number(product.price).toLocaleString()}</p>
            ${product.shipping_type === 'express' ?
            '<span style="display: inline-block; padding: 4px 8px; background: #28a745; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;"><i class="fa-solid fa-truck-fast"></i> Express</span>' :
            '<span style="display: inline-block; padding: 4px 8px; background: #6c757d; color: white; border-radius: 4px; font-size: 0.75em; font-weight: 600;"><i class="fa-solid fa-truck"></i> Freight</span>'
        }
            <div class="product-actions" style="display: flex; gap: 10px; justify-content: center; margin-top: 15px;">
                <a href="pdp?id=${product.id}"><button class="product-btn">View Details</button></a>
                <button class="wishlist-btn" onclick="toggleWishlist('${product.id}', this)" 
                    style="background: white; border: 1px solid #ddd; padding: 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">
                    <i class="fa-${product.in_wishlist ? 'solid' : 'regular'} fa-heart" 
                       style="color: ${product.in_wishlist ? '#ef4444' : '#666'}; font-size: 1.2rem;"></i>
                </button>
            </div>
        </div>
    `).join('');
}

// Update product count display
function updateProductCount(from, to, total) {
    const countElement = document.querySelector('.product-count');
    if (countElement) {
        countElement.innerHTML = `Showing <strong>${from}-${to}</strong> of <strong>${total}</strong> items`;
    }
}

// Update pagination buttons
function updatePagination(currentPage, totalPages) {
    const paginationContainer = document.querySelector('.pagination');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = '';

    // Previous button
    if (currentPage > 1) {
        html += `<button onclick="loadProducts(${currentPage - 1})" class="page-btn"><i class="fa-solid fa-angle-left"></i></button>`;
    }

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (i === currentPage) {
            html += `<button class="page-btn active">${i}</button>`;
        } else {
            html += `<button onclick="loadProducts(${i})" class="page-btn">${i}</button>`;
        }
    }

    // Next button
    if (currentPage < totalPages) {
        html += `<button onclick="loadProducts(${currentPage + 1})" class="page-btn"><i class="fa-solid fa-angle-right"></i></button>`;
    }

    paginationContainer.innerHTML = html;
}
