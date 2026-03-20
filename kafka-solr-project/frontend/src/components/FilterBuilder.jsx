import { useState } from 'react';
import { Plus, X, Filter } from 'lucide-react';
import { formatFieldName, getFieldType } from '../utils/fieldFormatter';

const OPERATORS = {
    string:  [{ v: 'exact', l: 'Equals' }, { v: 'contains', l: 'Contains' }, { v: 'starts', l: 'Starts with' }],
    integer: [{ v: 'exact', l: 'Equals' }, { v: 'range', l: 'Range' }, { v: 'gt', l: 'Greater than' }, { v: 'lt', l: 'Less than' }],
    float:   [{ v: 'exact', l: 'Equals' }, { v: 'range', l: 'Range' }, { v: 'gt', l: 'Greater than' }, { v: 'lt', l: 'Less than' }],
    boolean: [{ v: 'exact', l: 'Is' }],
    date:    [{ v: 'range', l: 'Date range' }, { v: 'exact', l: 'On date' }],
};

// Small inline AND/OR pill toggle shown between filter rows
function LogicToggle({ value, onChange }) {
    return (
        <div style={{
            display: 'flex',
            alignItems: 'center',
            gap: 6,
            padding: '2px 0',
        }}>
            {/* Connector line */}
            <div style={{ flex: 1, height: 1, background: 'var(--border)' }} />
            <div style={{
                display: 'flex',
                background: 'var(--bg-hover)',
                borderRadius: 6,
                border: '1px solid var(--border)',
                overflow: 'hidden',
            }}>
                {['AND', 'OR'].map(opt => (
                    <button
                        key={opt}
                        onClick={() => onChange(opt)}
                        style={{
                            padding: '2px 10px',
                            fontSize: 10,
                            fontWeight: 700,
                            border: 'none',
                            cursor: 'pointer',
                            letterSpacing: 0.5,
                            background: value === opt ? 'var(--accent)' : 'transparent',
                            color:      value === opt ? '#fff'          : 'var(--text-dim)',
                            transition: 'all 0.15s',
                        }}
                    >
                        {opt}
                    </button>
                ))}
            </div>
            <div style={{ flex: 1, height: 1, background: 'var(--border)' }} />
        </div>
    );
}

export default function FilterBuilder({ columns, filters, onChange, logic = 'AND', onLogicChange }) {
    const [open, setOpen] = useState(false);
    const [localFilters, setLocalFilters] = useState(filters);

    const toggleOpen = () => {
        if (!open) setLocalFilters(filters);
        setOpen(!open);
    };

    const addFilter = () => {
        const firstCol = columns[0] || '';
        setLocalFilters([...localFilters, {
            id:       Date.now(),
            field:    firstCol,
            type:     getFieldType(firstCol),
            operator: 'exact',
            value:    '',
            from:     '',
            to:       '',
            logic:    'AND',    // ← connector between this row and the previous one
        }]);
    };

    const removeFilter = (id) => setLocalFilters(localFilters.filter(f => f.id !== id));

    const updateFilter = (id, updates) => {
        setLocalFilters(localFilters.map(f => {
            if (f.id !== id) return f;
            const updated = { ...f, ...updates };
            if (updates.field) {
                updated.type     = getFieldType(updates.field);
                updated.operator = 'exact';
                updated.value    = '';
                updated.from     = '';
                updated.to       = '';
            }
            return updated;
        }));
    };

    const applyFilters = () => {
        onChange(localFilters);
        setOpen(false);
    };

    const activeCount = filters.filter(f => f.field && (f.value || f.from)).length;

    return (
        <div className="dropdown-panel">
            <button className="btn" onClick={toggleOpen}
                style={activeCount > 0 ? { borderColor: 'var(--accent)', color: 'var(--accent)' } : {}}>
                <Filter size={13} />
                Filters
                {activeCount > 0 && (
                    <span style={{
                        background: 'var(--accent)', color: '#fff',
                        borderRadius: 999, padding: '1px 6px', fontSize: 10,
                    }}>{activeCount}</span>
                )}
            </button>

            {open && (
                <div style={{
                    position: 'absolute', top: 'calc(100% + 6px)', left: 0, zIndex: 200,
                    background: 'var(--bg-card)', border: '1px solid var(--border)',
                    borderRadius: 12, padding: 16, minWidth: 640,
                    boxShadow: '0 8px 32px rgba(0,0,0,0.4)',
                }}>
                    {/* Header */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                        <span className="section-title">FILTER RULES</span>
                        <div style={{ display: 'flex', gap: 8 }}>
                            <button className="btn btn-sm" onClick={addFilter}><Plus size={12} /> Add Filter</button>
                            {localFilters.length > 0 && (
                                <button className="btn btn-sm btn-danger" onClick={() => setLocalFilters([])}>
                                    <X size={12} /> Clear All
                                </button>
                            )}
                            <button className="btn btn-sm btn-primary" onClick={applyFilters}>Done</button>
                        </div>
                    </div>

                    {localFilters.length === 0 && (
                        <div style={{ textAlign: 'center', padding: '20px', color: 'var(--text-dim)', fontSize: 13 }}>
                            No filters added. Click "Add Filter" to start filtering your data.
                        </div>
                    )}

                    <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
                        {localFilters.map((f, idx) => {
                            const ops = OPERATORS[f.type] || OPERATORS.string;
                            return (
                                <div key={f.id}>
                                    {/* AND / OR connector between rows (not before first row) */}
                                    {idx > 0 && (
                                        <LogicToggle
                                            value={f.logic || 'AND'}
                                            onChange={val => updateFilter(f.id, { logic: val })}
                                        />
                                    )}

                                    <div className="filter-row" style={{ marginTop: idx === 0 ? 0 : 4 }}>
                                        {/* WHERE label on first row */}
                                        <span style={{
                                            fontSize: 10, color: 'var(--text-dim)',
                                            width: 38, textAlign: 'center',
                                            fontFamily: 'var(--mono)', flexShrink: 0,
                                            padding: '3px 4px', fontWeight: 600,
                                        }}>
                                            {idx === 0 ? 'WHERE' : ''}
                                        </span>

                                        {/* Field selector */}
                                        <select className="input" value={f.field}
                                            onChange={e => updateFilter(f.id, { field: e.target.value })}
                                            style={{ flex: 2, minWidth: 160 }}>
                                            {columns.map(c => (
                                                <option key={c} value={c}>{formatFieldName(c)}</option>
                                            ))}
                                        </select>

                                        {/* Type badge */}
                                        <span style={{
                                            fontSize: 9, color: 'var(--text-dim)', fontFamily: 'var(--mono)',
                                            background: 'var(--bg-hover)', padding: '2px 6px', borderRadius: 4,
                                            flexShrink: 0, textTransform: 'uppercase', letterSpacing: 0.5,
                                        }}>
                                            {f.type}
                                        </span>

                                        {/* Operator */}
                                        <select className="input" value={f.operator}
                                            onChange={e => updateFilter(f.id, { operator: e.target.value })}
                                            style={{ flex: 1, minWidth: 120 }}>
                                            {ops.map(op => (
                                                <option key={op.v} value={op.v}>{op.l}</option>
                                            ))}
                                        </select>

                                        {/* Value input(s) */}
                                        {f.operator === 'range' ? (
                                            <>
                                                <input className="input" placeholder="From"
                                                    type={f.type === 'date' ? 'date' : 'text'}
                                                    value={f.from}
                                                    onChange={e => updateFilter(f.id, { from: e.target.value })}
                                                    style={{ flex: 1, minWidth: 90 }} />
                                                <span style={{ color: 'var(--text-dim)', fontSize: 12, flexShrink: 0 }}>to</span>
                                                <input className="input" placeholder="To"
                                                    type={f.type === 'date' ? 'date' : 'text'}
                                                    value={f.to}
                                                    onChange={e => updateFilter(f.id, { to: e.target.value })}
                                                    style={{ flex: 1, minWidth: 90 }} />
                                            </>
                                        ) : f.type === 'boolean' ? (
                                            <select className="input" value={f.value}
                                                onChange={e => updateFilter(f.id, { value: e.target.value })}
                                                style={{ flex: 2 }}>
                                                <option value="" disabled>Select...</option>
                                                <option value="true">True / Yes</option>
                                                <option value="false">False / No</option>
                                            </select>
                                        ) : (
                                            <input className="input" placeholder="Value..."
                                                value={f.value}
                                                onChange={e => updateFilter(f.id, { value: e.target.value })}
                                                onKeyDown={e => e.key === 'Enter' && applyFilters()}
                                                style={{ flex: 2 }} />
                                        )}

                                        {/* Remove button */}
                                        <button onClick={() => removeFilter(f.id)}
                                            style={{ background: 'none', border: 'none',
                                                color: 'var(--text-dim)', cursor: 'pointer', padding: 4,
                                                flexShrink: 0 }}>
                                            <X size={13} />
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            )}
        </div>
    );
}
