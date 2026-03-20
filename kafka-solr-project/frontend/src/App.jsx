import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import DataTable from './components/DataTable';
import FilterBuilder from './components/FilterBuilder';
import ChartRenderer from './components/ChartRenderer';
import ColumnSelector from './components/ColumnSelector';
import SavedViews from './components/SavedViews';
import DateRangeFilter from './components/DateRangeFilter';
import UploadCSV from './components/UploadCSV';
import { LayoutDashboard, BarChart3, BookmarkCheck, RefreshCw, Database } from 'lucide-react';
import './App.css';

import { formatFieldName, getFieldType } from './utils/fieldFormatter';

const API = 'http://localhost:8000';

export default function App() {
    const [data, setData]               = useState([]);
    const [total, setTotal]             = useState(0);
    const [globalColumns, setGlobalColumns] = useState([]);
    const [allColumns, setAllColumns]   = useState([]);
    const [visibleCols, setVisibleCols] = useState([]);
    const [filters, setFilters]         = useState([]);
    const [colWidths, setColWidths]     = useState(() => {
        try { return JSON.parse(localStorage.getItem('dataflow_colWidths') || '{}'); }
        catch { return {}; }
    });

    const [sort, setSort]               = useState('');
    const [page, setPage]               = useState(0);
    const [rows, setRows]               = useState(200);
    const [loading, setLoading]         = useState(false);
    const [activeTab, setActiveTab]     = useState('table');
    const [dateRange, setDateRange]     = useState({ from: '', to: '', field: '' });
    const [selectedFile, setSelectedFile] = useState('');
    const [sourceFiles, setSourceFiles] = useState([]);
    const [facets, setFacets]           = useState({});
    const [error, setError]             = useState('');
    const [schemaLoaded, setSchemaLoaded] = useState(false);

    // Persist colWidths to localStorage whenever it changes
    useEffect(() => {
        localStorage.setItem('dataflow_colWidths', JSON.stringify(colWidths));
    }, [colWidths]);

    // Load schema + sample doc + source files on mount
    useEffect(() => {
        Promise.all([
            axios.get(`${API}/schema.php`),
            axios.get(`${API}/search.php?q=*:*&rows=3&fl=*`),   // sample docs to capture real field names
            axios.get(`${API}/search.php?q=*:*&rows=0&facet=true&facet.field=source_file_s&facet.limit=200&facet.sort=index`),
        ])
        .then(([schemaRes, sampleRes, facetRes]) => {
            // ── Schema fields ─────────────────────────────────────────────────
            const schemaFields = (schemaRes.data.fields || [])
                .map(f => f.name)
                .filter(f => !['_version_', '_root_'].includes(f));

            // ── Sample-doc fields (catches dynamic fields Luke might miss) ────
            const sampleDocs  = sampleRes.data.response?.docs || [];
            const sampleFields = sampleDocs.flatMap(d => Object.keys(d))
                .filter(f => !['_version_', '_root_', 'score', 'id'].includes(f));

            // Merge: schema fields first, then any extras from real docs
            const merged = [...new Set([...schemaFields, ...sampleFields])];

            setGlobalColumns(merged);
            setAllColumns(merged);

            // Default visible columns
            const defaultCols = merged.filter(f =>
                ['product_id_i', 'Product_Name_s', 'Brand_Name_s', 'Price_f',
                 'Map_Price_f', 'Stock_s', 'source_file_s', 'Date_s', 'Date_dt'].includes(f)
            );
            setVisibleCols(defaultCols.length ? defaultCols : merged.slice(0, 8));
            setSchemaLoaded(true);

            // ── Source file facet ─────────────────────────────────────────────
            const rawFacets = facetRes.data.facet_counts?.facet_fields?.source_file_s || [];
            const files = [];
            for (let i = 0; i < rawFacets.length; i += 2) files.push(rawFacets[i]);
            setSourceFiles(files.sort());
        })
        .catch(() => {
            setError('Cannot connect to PHP API at ' + API + '. Make sure docker-compose is running.');
        });
    }, []);

    // State and Logic for fetching data
    const fetchData = useCallback(async () => {
        if (!schemaLoaded) return;
        setLoading(true);
        setError('');
        try {
            const facetFields = allColumns.filter(c => c.endsWith('_s') &&
                ['Stock_s', 'source_file_s', 'Brand_Name_s', 'Type_s'].includes(c)
            ).slice(0, 3);

            const res = await axios.post(`${API}/search.php`, {
                q:           '*:*',
                start:       page * rows,
                rows,
                fl:          '*',
                sort:        sort || undefined,
                facet:       facetFields.length ? 'true' : undefined,
                facet_field: facetFields,
                // These are now handled safely by the backend
                filters:      filters,
                dateRange:    dateRange,
                selectedFile: selectedFile,
            });

            const solr = res.data;
            setData(solr.response?.docs ?? []);
            setTotal(solr.response?.numFound ?? 0);
            if (solr.facet_counts?.facet_fields) {
                setFacets(solr.facet_counts.facet_fields);
            }
        } catch (e) {
            console.error("Fetch Data Error:", e);
            setError('Failed to fetch data from Solr. Check server logs.');
        } finally {
            setLoading(false);
        }
    }, [schemaLoaded, allColumns, page, rows, sort, filters, dateRange, selectedFile]);


    // When selectedFile changes, fetch its exact columns
    useEffect(() => {
        if (!selectedFile) {
            setAllColumns(globalColumns);
            // Reset to default core columns for global view
            const defaults = globalColumns.filter(f =>
                ['product_id_i', 'Product_Name_s', 'Brand_Name_s', 'Price_f',
                 'Map_Price_f', 'Stock_s', 'source_file_s', 'Date_s'].includes(f)
            );
            if (defaults.length > 0) setVisibleCols(defaults);
            return;
        }
        axios.get(`${API}/search.php`, {
            params: { q: `source_file_s:"${selectedFile}"`, rows: 1, fl: '*' }
        }).then(res => {
            const docs = res.data.response?.docs || [];
            if (docs.length > 0) {
                const docKeys = Object.keys(docs[0]).filter(f => !['_version_', '_root_'].includes(f));

                // Always merge in _dt fields from the global schema so the
                // DateRangeFilter never loses date columns when a file has no Date column
                const globalDateCols = globalColumns.filter(c => c.endsWith('_dt'));
                const merged = [...new Set([...docKeys, ...globalDateCols])];

                setAllColumns(merged);
                
                // Select ALL columns of the selected file to view
                setVisibleCols(docKeys);
            }
        }).catch(e => console.error("Could not fetch file columns", e));
    }, [selectedFile, globalColumns]);

    useEffect(() => { fetchData(); }, [fetchData]);

    const handleSavedView = (view) => {
        if (view.columns?.length)   setVisibleCols(view.columns);
        if (view.filters?.length)   setFilters(view.filters);
        if (view.sort)              setSort(view.sort);
        if (view.colWidths)         setColWidths(view.colWidths);
        setPage(0);
        setActiveTab('table');
    };

    const handleChartFilter = (field, value) => {
        if (!field || !value) return;
        setFilters(prev => [
            ...prev,
            {
                id: Date.now(),
                field,
                type: getFieldType(field),
                operator: 'exact',
                value: String(value),
                from: '',
                to: ''
            }
        ]);
        setPage(0);
        setActiveTab('table');
    };

    const tabs = [
        { id: 'table',  label: 'Data Table', icon: LayoutDashboard },
        { id: 'charts', label: 'Charts',      icon: BarChart3 },
        { id: 'saved',  label: 'Saved Views', icon: BookmarkCheck },
    ];

    return (
        <div className="app">
            {/* Header */}
            <header className="app-header">
                <div className="header-left">
                    <div className="logo">
                        <span className="logo-icon">⚡</span>
                        <span className="logo-text">DataFlow</span>
                        <span className="logo-sub">Reporting</span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <span className="stat-badge">
                            <Database size={10} style={{ marginRight: 4 }} />
                            {total.toLocaleString()} docs
                        </span>
                        {allColumns.length > 0 && (
                            <span className="stat-badge" style={{ borderColor: 'var(--accent2)', color: 'var(--accent2)', background: 'rgba(34,211,238,0.1)' }}>
                                {allColumns.length} fields
                            </span>
                        )}
                    </div>
                </div>

                <nav className="header-tabs">
                    {tabs.map(t => (
                        <button key={t.id} className={`tab-btn ${activeTab === t.id ? 'active' : ''}`}
                            onClick={() => setActiveTab(t.id)}>
                            <t.icon size={14} />
                            {t.label}
                        </button>
                    ))}
                </nav>

                <UploadCSV onUploaded={() => {
                    fetchData();
                    // Also refresh source files list
                    axios.get(`${API}/search.php?q=*:*&rows=0&facet=true&facet.field=source_file_s&facet.limit=200&facet.sort=index`)
                        .then(res => {
                            const rawFacets = res.data.facet_counts?.facet_fields?.source_file_s || [];
                            const files = [];
                            for (let i = 0; i < rawFacets.length; i += 2) files.push(rawFacets[i]);
                            setSourceFiles(files.sort());
                        });
                }} />

                <button className="refresh-btn" onClick={fetchData} disabled={loading}>
                    <RefreshCw size={13} className={loading ? 'spinning' : ''} />
                    Refresh
                </button>
            </header>

            {/* Error bar */}
            {error && (
                <div className="error-bar">
                    ⚠️ {error}
                </div>
            )}

            {/* Toolbar */}
            <div className="toolbar">
                <div style={{ paddingRight: 12, borderRight: '1px solid var(--border)' }}>
                    <select className="input" style={{ width: 180 }}
                        value={selectedFile}
                        onChange={e => { setSelectedFile(e.target.value); setPage(0); }}>
                        <option value="">All Source Files</option>
                        {sourceFiles.map(f => (
                            <option key={f} value={f}>{f}</option>
                        ))}
                    </select>
                </div>
                <FilterBuilder
                    columns={allColumns}
                    filters={filters}
                    onChange={f => { setFilters(f); setPage(0); }}
                />
                <DateRangeFilter
                    columns={allColumns}
                    value={dateRange}
                    onChange={v => { setDateRange(v); setPage(0); }}
                    matchCount={dateRange.field && dateRange.from ? total : null}
                />
                <ColumnSelector
                    columns={allColumns}
                    visible={visibleCols}
                    onChange={setVisibleCols}
                />

                {/* Active filter chips */}
                {filters.filter(f => f.field && (f.value || f.from)).map(f => (
                    <div key={f.id} style={{
                        display: 'flex', alignItems: 'center', gap: 6,
                        padding: '4px 10px', background: 'rgba(99,102,241,0.1)',
                        border: '1px solid rgba(99,102,241,0.3)', borderRadius: 999,
                        fontSize: 11, color: 'var(--accent)',
                    }}>
                        <span style={{ color: 'var(--text-muted)' }}>{f.field.replace(/_[sifb]$/, '')}</span>
                        <span>{f.operator === 'range' ? `${f.from}–${f.to}` : f.value}</span>
                        <button onClick={() => setFilters(filters.filter(x => x.id !== f.id))}
                            style={{ background: 'none', border: 'none', color: 'var(--accent)', cursor: 'pointer', padding: 0, lineHeight: 1 }}>
                            ×
                        </button>
                    </div>
                ))}
            </div>

            {/* Main content */}
            <main className="main-content">
                {activeTab === 'table' && (
                    <DataTable
                        data={data}
                        columns={visibleCols}
                        total={total}
                        page={page}
                        rows={rows}
                        onRowsChange={setRows}
                        onColumnReorder={setVisibleCols}
                        loading={loading}
                        sort={sort}
                        onSort={setSort}
                        onPage={setPage}
                        facets={facets}
                        colWidths={colWidths}
                        onColWidthsChange={setColWidths}
                    />
                )}
                {activeTab === 'charts' && (
                    <ChartRenderer 
                        data={data} 
                        columns={visibleCols} 
                        total={total} 
                        onFilter={handleChartFilter}
                    />
                )}
                {activeTab === 'saved' && (
                    <SavedViews
                        currentFilters={filters}
                        currentColumns={visibleCols}
                        currentSort={sort}
                        currentColWidths={colWidths}
                        onLoad={handleSavedView}
                    />
                )}
            </main>
        </div>
    );
}
