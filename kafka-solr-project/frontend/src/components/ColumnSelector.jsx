import { useState } from 'react';
import { Columns, Check, ChevronDown, ChevronRight } from 'lucide-react';
import { formatFieldName, groupFields } from '../utils/fieldFormatter';

export default function ColumnSelector({ columns, visible, onChange }) {
    const [open, setOpen]         = useState(false);
    const [search, setSearch]     = useState('');
    const [collapsed, setCollapsed] = useState({});

    const toggle = (col) => {
        if (visible.includes(col)) {
            if (visible.length <= 1) return;
            onChange(visible.filter(c => c !== col));
        } else {
            onChange([...visible, col]);
        }
    };

    const toggleGroup = (groupName, groupCols) => {
        const allVisible = groupCols.every(c => visible.includes(c));
        if (allVisible) {
            const remaining = visible.filter(c => !groupCols.includes(c));
            onChange(remaining.length ? remaining : visible);
        } else {
            const merged = [...new Set([...visible, ...groupCols])];
            onChange(merged);
        }
    };

    const toggleCollapse = (group) => {
        setCollapsed(prev => ({ ...prev, [group]: !prev[group] }));
    };

    const filtered = search
        ? columns.filter(c =>
            c.toLowerCase().includes(search.toLowerCase()) ||
            formatFieldName(c).toLowerCase().includes(search.toLowerCase())
          )
        : null;

    const groups = groupFields(columns);

    const groupColors = {
        Core:    '#6366f1',
        Pricing: '#10b981',
        MAP:     '#ef4444',
        Media:   '#f59e0b',
        Meta:    '#6b6b80',
        AF:      '#6366f1',
        BFD:     '#22d3ee',
        EMS:     '#10b981',
        HGS:     '#f59e0b',
    };

    return (
        <div className="dropdown-panel">
            <button className="btn" onClick={() => setOpen(!open)}>
                <Columns size={13} />
                Columns
                <span style={{ color: 'var(--text-dim)', fontSize: 11, fontFamily: 'var(--mono)' }}>
                    {visible.length}/{columns.length}
                </span>
            </button>

            {open && (
                <div className="col-panel" style={{ width: 300 }}>
                    {/* Search */}
                    <input className="input" placeholder="Search columns..."
                        value={search} onChange={e => setSearch(e.target.value)}
                        style={{ width: '100%', fontSize: 12, padding: '6px 10px', marginBottom: 8 }} />

                    {/* Actions */}
                    <div style={{ display: 'flex', gap: 6, marginBottom: 10 }}>
                        <button className="btn btn-sm" onClick={() => onChange([...columns])}
                            style={{ flex: 1, justifyContent: 'center', fontSize: 11 }}>All</button>
                        <button className="btn btn-sm" onClick={() => onChange(columns.slice(0, 1))}
                            style={{ flex: 1, justifyContent: 'center', fontSize: 11 }}>None</button>
                        <button className="btn btn-sm btn-primary" onClick={() => setOpen(false)}
                            style={{ flex: 1, justifyContent: 'center', fontSize: 11 }}>Done</button>
                    </div>

                    {/* Search results */}
                    {search ? (
                        filtered.map(col => (
                            <ColItem key={col} col={col} visible={visible} toggle={toggle} />
                        ))
                    ) : (
                        // Grouped columns
                        Object.entries(groups).map(([groupName, groupCols]) => {
                            const color      = groupColors[groupName] || '#8b8baa';
                            const allVisible = groupCols.every(c => visible.includes(c));
                            const someVisible = groupCols.some(c => visible.includes(c));
                            const isCollapsed = collapsed[groupName];

                            return (
                                <div key={groupName} style={{ marginBottom: 4 }}>
                                    {/* Group header */}
                                    <div style={{
                                        display: 'flex', alignItems: 'center', gap: 6,
                                        padding: '5px 6px', borderRadius: 6,
                                        background: 'var(--bg-hover)', marginBottom: 2,
                                        cursor: 'pointer',
                                    }}>
                                        {/* Collapse toggle */}
                                        <span onClick={() => toggleCollapse(groupName)}
                                            style={{ color: 'var(--text-dim)', flexShrink: 0 }}>
                                            {isCollapsed
                                                ? <ChevronRight size={12} />
                                                : <ChevronDown size={12} />}
                                        </span>

                                        {/* Group checkbox */}
                                        <div onClick={() => toggleGroup(groupName, groupCols)}
                                            style={{
                                                width: 14, height: 14, borderRadius: 3, flexShrink: 0,
                                                border: `1px solid ${allVisible ? color : 'var(--border)'}`,
                                                background: allVisible ? color : someVisible ? color + '40' : 'var(--bg-secondary)',
                                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                cursor: 'pointer',
                                            }}>
                                            {allVisible && <Check size={9} color="#fff" />}
                                            {someVisible && !allVisible && (
                                                <div style={{ width: 6, height: 2, background: color, borderRadius: 1 }} />
                                            )}
                                        </div>

                                        {/* Group name */}
                                        <span onClick={() => toggleCollapse(groupName)} style={{
                                            flex: 1, fontSize: 11, fontWeight: 600,
                                            color, textTransform: 'uppercase', letterSpacing: 0.5,
                                        }}>
                                            {groupName}
                                        </span>

                                        <span style={{ fontSize: 10, color: 'var(--text-dim)', fontFamily: 'var(--mono)' }}>
                                            {groupCols.filter(c => visible.includes(c)).length}/{groupCols.length}
                                        </span>
                                    </div>

                                    {/* Group columns */}
                                    {!isCollapsed && (
                                        <div style={{ paddingLeft: 20 }}>
                                            {groupCols.map(col => (
                                                <ColItem key={col} col={col} visible={visible} toggle={toggle} />
                                            ))}
                                        </div>
                                    )}
                                </div>
                            );
                        })
                    )}
                </div>
            )}
        </div>
    );
}

function ColItem({ col, visible, toggle }) {
    const checked = visible.includes(col);
    return (
        <div className={`col-item ${checked ? 'checked' : ''}`} onClick={() => toggle(col)}>
            <div className={`checkbox ${checked ? 'checked' : ''}`}>
                {checked && <Check size={8} color="#fff" />}
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 12, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {formatFieldName(col)}
                </div>
                <div style={{ fontSize: 10, color: 'var(--text-dim)', fontFamily: 'var(--mono)',
                    overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {col}
                </div>
            </div>
        </div>
    );
}
