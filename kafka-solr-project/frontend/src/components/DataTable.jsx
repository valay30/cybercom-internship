import { useState, useEffect } from 'react';
import { ArrowUpDown, ArrowUp, ArrowDown, Download, ExternalLink, Image } from 'lucide-react';
import { formatFieldName, formatValue, getRetailerPrefix } from '../utils/fieldFormatter';

export default function DataTable({ data, columns, total, page, rows, loading, sort, onSort, onPage, onRowsChange, onColumnReorder, facets, colWidths = {}, onColWidthsChange }) {
    const totalPages = Math.ceil(total / rows);
    const startRow   = page * rows + 1;
    const endRow     = Math.min((page + 1) * rows, total);

    const [draggedColIdx, setDraggedColIdx] = useState(null);
    const [dragOverColIdx, setDragOverColIdx] = useState(null);

    const [isResizing, setIsResizing] = useState(false);

    const handleSort = (col) => {
        if (sort === `${col} asc`) onSort(`${col} desc`);
        else if (sort === `${col} desc`) onSort('');
        else onSort(`${col} asc`);
    };

    const getSortIcon = (col) => {
        if (sort === `${col} asc`) return <ArrowUp size={11} />;
        if (sort === `${col} desc`) return <ArrowDown size={11} />;
        return <ArrowUpDown size={11} style={{ opacity: 0.25 }} />;
    };

    const exportCSV = () => {
        if (!data.length) return;
        const header  = columns.map(formatFieldName).join(',');
        const rowsStr = data.map(row => columns.map(c => `"${(row[c] ?? '')}"`).join(',')).join('\n');
        const blob    = new Blob([header + '\n' + rowsStr], { type: 'text/csv' });
        const url     = URL.createObjectURL(blob);
        const a       = document.createElement('a');
        a.href = url; a.download = 'export.csv'; a.click();
        URL.revokeObjectURL(url);
    };

    const getPageNums = () => {
        const pages = [];
        const start = Math.max(0, page - 2);
        const end   = Math.min(totalPages - 1, page + 2);
        for (let i = start; i <= end; i++) pages.push(i);
        return pages;
    };

    // Get column header color based on retailer prefix
    const getColColor = (col) => {
        const prefix = getRetailerPrefix(col);
        const colors = {
            AF:  '#6366f1', BFD: '#22d3ee', EMS: '#10b981',
            HGS: '#f59e0b', WMT: '#ef4444', AMZ: '#f97316',
        };
        return prefix ? (colors[prefix] || '#8b8baa') : null;
    };

    return (
        <div className="card" style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>

            {/* Top bar */}
            <div style={{
                display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                padding: '10px 16px', borderBottom: '1px solid var(--border)',
                background: 'var(--bg-secondary)',
            }}>
                <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                    <span style={{ fontSize: 12, color: 'var(--text-muted)' }}>
                        {loading ? 'Loading...' : (
                            <>
                                <span style={{ color: 'var(--text-primary)', fontWeight: 600 }}>
                                    {startRow.toLocaleString()}–{endRow.toLocaleString()}
                                </span>
                                <span style={{ margin: '0 4px' }}>of</span>
                                <span style={{ color: 'var(--accent)', fontWeight: 600 }}>
                                    {total.toLocaleString()}
                                </span>
                                <span style={{ marginLeft: 4 }}>results</span>
                            </>
                        )}
                    </span>
                    {/* Column count badge */}
                    <span style={{
                        fontSize: 11, color: 'var(--text-dim)', fontFamily: 'var(--mono)',
                        background: 'var(--bg-hover)', padding: '2px 8px', borderRadius: 999,
                        border: '1px solid var(--border)'
                    }}>
                        {columns.length} columns
                    </span>
                </div>
                <button className="btn btn-sm" onClick={exportCSV} disabled={!data.length}>
                    <Download size={12} /> Export CSV
                </button>
            </div>

            {/* Facets bar */}
            {Object.keys(facets).length > 0 && (
                <div style={{
                    padding: '6px 16px', borderBottom: '1px solid var(--border-light)',
                    display: 'flex', flexWrap: 'wrap', gap: 5, alignItems: 'center',
                }}>
                    <span style={{ fontSize: 10, color: 'var(--text-dim)', textTransform: 'uppercase', letterSpacing: 1 }}>
                        Top values:
                    </span>
                    {Object.entries(facets).slice(0, 2).map(([field, values]) =>
                        values.filter((_, i) => i % 2 === 0).slice(0, 4).map((val, i) => (
                            <span key={field + val} className="facet-badge">
                                {String(val).slice(0, 18)}
                                <span style={{ color: 'var(--accent)', fontFamily: 'var(--mono)', fontSize: 9, marginLeft: 3 }}>
                                    {values[i * 2 + 1]}
                                </span>
                            </span>
                        ))
                    )}
                </div>
            )}

            {/* Table */}
            <div className="table-wrap" style={{ flex: 1, overflowY: 'auto' }}>
                {loading ? (
                    <SkeletonRows columns={columns} />
                ) : data.length === 0 ? (
                    <EmptyState />
                ) : (
                    <table>
                        <thead>
                            <tr>
                                <th style={{ width: 36, textAlign: 'center', color: 'var(--text-dim)', fontSize: 10 }}>
                                    #
                                </th>
                                {columns.map((col, idx) => {
                                    const color = getColColor(col);
                                    return (
                                        <th key={col} title={col}
                                            draggable={!isResizing}
                                            onDragStart={(e) => {
                                                if (isResizing) return;
                                                setDraggedColIdx(idx);
                                                e.dataTransfer.effectAllowed = "move";
                                            }}
                                            onDragOver={(e) => {
                                                e.preventDefault();
                                                e.dataTransfer.dropEffect = "move";
                                                if (dragOverColIdx !== idx) setDragOverColIdx(idx);
                                            }}
                                            onDrop={(e) => {
                                                e.preventDefault();
                                                setDragOverColIdx(null);
                                                if (draggedColIdx === null || draggedColIdx === idx) return;
                                                const newCols = [...columns];
                                                const [moved] = newCols.splice(draggedColIdx, 1);
                                                newCols.splice(idx, 0, moved);
                                                if (onColumnReorder) onColumnReorder(newCols);
                                                setDraggedColIdx(null);
                                            }}
                                            onDragEnd={() => {
                                                setDraggedColIdx(null);
                                                setDragOverColIdx(null);
                                            }}
                                            onDragEnter={(e) => e.preventDefault()}
                                            style={{ 
                                                position: 'relative',
                                                cursor: isResizing ? 'col-resize' : 'grab',
                                                width: colWidths[col] ? `${colWidths[col]}px` : undefined,
                                                minWidth: colWidths[col] ? `${colWidths[col]}px` : 100,
                                                background: dragOverColIdx === idx ? 'var(--bg-hover)' : undefined,
                                                opacity: draggedColIdx === idx ? 0.4 : 1,
                                                boxShadow: dragOverColIdx === idx && dragOverColIdx < draggedColIdx ? 'inset 3px 0 0 var(--accent)' :
                                                           dragOverColIdx === idx && dragOverColIdx > draggedColIdx ? 'inset -3px 0 0 var(--accent)' : undefined,
                                            }}>
                                            {color && (
                                                <div style={{
                                                    position: 'absolute', top: 0, left: 0, right: 0,
                                                    height: 2, background: color, borderRadius: '2px 2px 0 0'
                                                }} />
                                            )}
                                            <div onClick={() => !isResizing && handleSort(col)} style={{ display: 'flex', alignItems: 'center', gap: 4, paddingTop: color ? 4 : 0 }}>
                                                <span style={{ color: color || undefined, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                                                    {formatFieldName(col)}
                                                </span>
                                                {getSortIcon(col)}
                                            </div>
                                            
                                            {/* Column Resizer Handle */}
                                            <div
                                                title="Double-click to expand, drag to resize"
                                                onMouseDown={(e) => {
                                                    e.preventDefault();
                                                    e.stopPropagation();
                                                    setIsResizing(true);

                                                    const th = e.target.closest('th');
                                                    const startWidth = th.offsetWidth;
                                                    const startX = e.pageX;

                                                    const onMouseMove = (moveEvent) => {
                                                        const newWidth = Math.max(60, startWidth + (moveEvent.pageX - startX));
                                                        if (onColWidthsChange) onColWidthsChange(prev => ({ ...prev, [col]: newWidth }));
                                                    };

                                                    const onMouseUp = () => {
                                                        document.removeEventListener('mousemove', onMouseMove);
                                                        document.removeEventListener('mouseup', onMouseUp);
                                                        setTimeout(() => setIsResizing(false), 50);
                                                    };

                                                    document.addEventListener('mousemove', onMouseMove);
                                                    document.addEventListener('mouseup', onMouseUp);
                                                }}
                                                style={{
                                                    position: 'absolute', right: -4, top: 0, bottom: 0,
                                                    width: 8, cursor: 'col-resize', zIndex: 10,
                                                }}
                                                onMouseEnter={e => e.currentTarget.style.background = 'var(--accent)'}
                                                onMouseLeave={e => e.currentTarget.style.background = 'transparent'}
                                            />
                                        </th>
                                    );
                                })}
                            </tr>
                        </thead>
                        <tbody>
                            {data.map((row, i) => (
                                <tr key={row.id || i}>
                                    <td style={{ textAlign: 'center', color: 'var(--text-dim)', fontSize: 10, width: 36 }}>
                                        {startRow + i}
                                    </td>
                                    {columns.map(col => (
                                        <td key={col}>
                                            <CellRenderer field={col} value={row[col]} />
                                        </td>
                                    ))}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>

            {/* Pagination */}
            {!loading && total > 0 && (
                <div className="pagination">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                        <span style={{ fontSize: 12 }}>
                            Page <strong>{page + 1}</strong> of <strong>{totalPages.toLocaleString() || 1}</strong>
                        </span>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <span style={{ fontSize: 11, color: 'var(--text-dim)' }}>Rows per page:</span>
                            <select 
                                className="input" 
                                style={{ padding: '2px 24px 2px 8px', fontSize: 11, height: 26, minHeight: 26 }}
                                value={rows} 
                                onChange={(e) => {
                                    onRowsChange(Number(e.target.value));
                                    onPage(0);
                                }}
                            >
                                <option value={20}>20</option>
                                <option value={50}>50</option>
                                <option value={100}>100</option>
                                <option value={200}>200</option>
                                <option value={500}>500</option>
                            </select>
                        </div>
                    </div>
                    {totalPages > 1 && (
                        <div className="page-controls">
                            <button className="page-btn" onClick={() => onPage(0)} disabled={page === 0}>«</button>
                            <button className="page-btn" onClick={() => onPage(page - 1)} disabled={page === 0}>‹</button>
                            {getPageNums().map(p => (
                                <button key={p} className={`page-btn ${p === page ? 'active' : ''}`} onClick={() => onPage(p)}>
                                    {p + 1}
                                </button>
                            ))}
                            <button className="page-btn" onClick={() => onPage(page + 1)} disabled={page >= totalPages - 1}>›</button>
                            <button className="page-btn" onClick={() => onPage(totalPages - 1)} disabled={page >= totalPages - 1}>»</button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

// ─── Cell Renderer ────────────────────────────────────────────────────────────

function CellRenderer({ field, value }) {
    const [imgError, setImgError] = useState(false);
    const formatted = formatValue(field, value);

    // Empty value
    if (!formatted) {
        return <span style={{ color: 'var(--text-dim)', fontSize: 11 }}>—</span>;
    }

    const { type, display } = formatted;

// Show raw URL text — truncated with full URL in tooltip
if (type === 'url') {
    return (
        <span title={display} style={{
            fontFamily: 'var(--mono)',
            fontSize: 11,
            color: 'var(--text-muted)',
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            whiteSpace: 'nowrap',
            display: 'block',
            maxWidth: 260,
        }}>
            {display}
        </span>
    );
}

    // Price
    if (type === 'price') {
        return (
            <span style={{ color: '#10b981', fontFamily: 'var(--mono)', fontSize: 12, fontWeight: 600 }}>
                {display}
            </span>
        );
    }

    // Stock status
    if (type === 'stock') {
        const inStock = display === 'In Stock';
        return (
            <span style={{
                display: 'inline-flex', alignItems: 'center', gap: 4,
                color: inStock ? '#10b981' : '#ef4444',
                fontSize: 11, fontWeight: 500,
            }}>
                <span style={{
                    width: 6, height: 6, borderRadius: '50%',
                    background: inStock ? '#10b981' : '#ef4444',
                    flexShrink: 0,
                }} />
                {display}
            </span>
        );
    }

    // MAP Violation status
    if (type === 'violation') {
        const color = display === 'Resolved' ? '#10b981' :
                      display === 'Active'   ? '#ef4444' : '#f59e0b';
        return (
            <span style={{
                display: 'inline-block',
                padding: '2px 8px', borderRadius: 999,
                background: color + '18', border: `1px solid ${color}40`,
                color, fontSize: 11, fontWeight: 500,
            }}>
                {display}
            </span>
        );
    }

    // Boolean
    if (type === 'boolean') {
        return (
            <span style={{ color: display ? '#10b981' : '#ef4444', fontWeight: 600 }}>
                {display ? '✓' : '✗'}
            </span>
        );
    }

    // Number
    if (type === 'number') {
        return (
            <span style={{ color: 'var(--accent2)', fontFamily: 'var(--mono)', fontSize: 12 }}>
                {display}
            </span>
        );
    }

    // Long text — truncate with tooltip
    if (type === 'long') {
        return (
            <span title={display} style={{ cursor: 'help' }}>
                {display.slice(0, 55)}
                <span style={{ color: 'var(--text-dim)' }}>…</span>
            </span>
        );
    }

    // Default text
    return <span>{display}</span>;
}

// ─── Skeleton loader ──────────────────────────────────────────────────────────
function SkeletonRows({ columns }) {
    return (
        <div style={{ padding: 16 }}>
            {[...Array(10)].map((_, i) => (
                <div key={i} style={{ display: 'flex', gap: 10, marginBottom: 10, opacity: 1 - i * 0.08 }}>
                    {[...Array(Math.min(columns.length + 1, 7))].map((_, j) => (
                        <div key={j} className="skeleton" style={{
                            flex: j === 0 ? '0 0 30px' : 1,
                            height: 11,
                            animationDelay: `${j * 0.05}s`
                        }} />
                    ))}
                </div>
            ))}
        </div>
    );
}

// ─── Empty state ──────────────────────────────────────────────────────────────
function EmptyState() {
    return (
        <div className="empty-state">
            <div className="empty-state-icon">📭</div>
            <h3>No results found</h3>
            <p>Try adjusting your filters or clearing the search</p>
        </div>
    );
}
