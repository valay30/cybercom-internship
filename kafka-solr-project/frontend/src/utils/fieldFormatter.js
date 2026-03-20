// ─── Field display name formatter ────────────────────────────────────────────

export function formatFieldName(field) {
    // Remove Solr type suffix
    let name = field
        .replace(/_s$/, '')
        .replace(/_i$/, '')
        .replace(/_f$/, '')
        .replace(/_b$/, '')
        .replace(/_dt$/, '');

    // Replace underscores with spaces
    name = name.replace(/_/g, ' ');

    // Title case
    name = name.replace(/\b\w/g, c => c.toUpperCase());

    // Fix common abbreviations
    name = name
        .replace(/\bAf\b/g, 'AF')
        .replace(/\bBfd\b/g, 'BFD')
        .replace(/\bEms\b/g, 'EMS')
        .replace(/\bHgs\b/g, 'HGS')
        .replace(/\bMap\b/g, 'MAP')
        .replace(/\bUrl\b/g, 'URL')
        .replace(/\bSku\b/g, 'SKU')
        .replace(/\bId\b/g, 'ID');

    return name.trim();
}

// Get retailer prefix from field name e.g. "AF_URL_s" → "AF"
export function getRetailerPrefix(field) {
    const match = field.match(/^([A-Z]+)_/);
    if (!match) return null;
    const prefix = match[1];
    // Only return if it looks like a retailer code (2-5 uppercase letters)
    if (/^[A-Z]{2,5}$/.test(prefix) && !['MAP', 'URL', 'SKU'].includes(prefix)) {
        return prefix;
    }
    return null;
}

// Get field type from suffix
export function getFieldType(field) {
    if (field.endsWith('_i'))  return 'integer';
    if (field.endsWith('_f'))  return 'float';
    if (field.endsWith('_b'))  return 'boolean';
    if (field.endsWith('_dt')) return 'date';
    return 'string';
}

// Format cell value based on field name and type
export function formatValue(field, value) {
    if (value === null || value === undefined || value === '') return null;

    const lower = field.toLowerCase();

    // Price fields
    if (lower.includes('price') && field.endsWith('_f')) {
        return { type: 'price', display: '$' + Number(value).toFixed(2) };
    }

    // Stock field
    if (lower.includes('stock')) {
        return { type: 'stock', display: value };
    }

    // MAP Violation status
    if (lower.includes('violation') && !lower.includes('date') && !lower.includes('screenshot')) {
        return { type: 'violation', display: value };
    }

    // Boolean
    if (field.endsWith('_b')) {
        return { type: 'boolean', display: value === 'true' || value === true };
    }

    // Integer
    if (field.endsWith('_i')) {
        return { type: 'number', display: Number(value).toLocaleString() };
    }

    // Float
    if (field.endsWith('_f')) {
        return { type: 'number', display: Number(value).toFixed(2) };
    }

    // Long string
    if (typeof value === 'string' && value.length > 60) {
        return { type: 'long', display: value };
    }

    return { type: 'text', display: String(value) };
}

// Group fields by category for better display
export function groupFields(fields) {
    const groups = {
        'Core':      [],
        'Pricing':   [],
        'Retailer':  [],
        'MAP':       [],
        'Media':     [],
        'Meta':      [],
    };

    const coreFields    = ['product_id_i', 'parent_sku_s', 'Product_Name_s', 'Type_s', 'Brand_Name_s', 'Default_SKU_s', 'Priority_s', 'Priority_i'];
    const priceFields   = ['Price_f', 'Map_Price_f'];
    const mapFields     = ['MAP_Violation_s', 'MAP_Violation_Date_Time_s', 'MAP_Violation_Screenshot_s', 'MAP_Violation_Screenshot_Date_Time_s'];
    const mediaFields   = ['Image_URL_s', 'Extracted_URL_s'];
    const metaFields    = ['source_file_s', 'Date_s', 'Stock_s', 'Last_Price_Change_s', 'Quantity_i', 'row_number_i'];

    fields.forEach(field => {
        if (coreFields.includes(field))   { groups['Core'].push(field); return; }
        if (priceFields.includes(field))  { groups['Pricing'].push(field); return; }
        if (mapFields.includes(field))    { groups['MAP'].push(field); return; }
        if (mediaFields.includes(field))  { groups['Media'].push(field); return; }
        if (metaFields.includes(field))   { groups['Meta'].push(field); return; }

        // Retailer-specific fields (have prefix like AF_, BFD_, EMS_)
        const retailer = getRetailerPrefix(field);
        if (retailer) {
            if (!groups[retailer]) groups[retailer] = [];
            groups[retailer].push(field);
            return;
        }

        groups['Meta'].push(field);
    });

    // Remove empty groups
    return Object.fromEntries(Object.entries(groups).filter(([, v]) => v.length > 0));
}
