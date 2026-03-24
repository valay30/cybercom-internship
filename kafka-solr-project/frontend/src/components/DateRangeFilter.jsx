import { useState, useEffect } from 'react';
import { Calendar, X } from 'lucide-react';

export default function DateRangeFilter({ columns, value, onChange, matchCount }) {
  const [open, setOpen] = useState(false);

  // ── Local draft — not applied until Done is clicked ──────────────────────
  const [draft, setDraft] = useState({ field: value.field, from: value.from, to: value.to });

  // Sync draft whenever the panel is opened (so it reflects the last-applied state)
  useEffect(() => {
    if (open) {
      setDraft({ field: value.field, from: value.from, to: value.to });
    }
  }, [open]);

  // Collect all date-related columns from the provided list
  const dateCols = [
    // Always include ingested_at_dt — every indexed document has this field
    'ingested_at_dt',
    // Dynamic date columns from the schema / sample docs
    ...columns.filter(c =>
      c !== 'ingested_at_dt' && (
        c.endsWith('_dt') ||
        c.toLowerCase().includes('date') ||
        c.toLowerCase().includes('time')
      )
    ),
  ];

  const hasActive = value.field && value.from;

  // Apply draft → parent and close panel
  const handleDone = () => {
    onChange(draft);
    setOpen(false);
  };

  // Clear both draft and applied state
  const handleClear = () => {
    const empty = { from: '', to: '', field: '' };
    setDraft(empty);
    onChange(empty);
  };

  // Quick-select presets update draft only
  const applyPreset = (days) => {
    const to = new Date();
    const from = new Date(Date.now() - days * 86400000);
    setDraft(prev => ({
      field: prev.field || dateCols[0] || '',
      from: from.toISOString().split('T')[0],
      to: to.toISOString().split('T')[0],
    }));
  };

  const draftIsApplied =
    draft.field === value.field &&
    draft.from === value.from &&
    draft.to === value.to;

  return (
    <div className="dropdown-panel">
      {/* Trigger button */}
      <button
        className="btn"
        onClick={() => setOpen(!open)}
        style={hasActive ? { borderColor: 'var(--accent2)', color: 'var(--accent2)' } : {}}
      >
        <Calendar size={13} />
        Date Range
        {hasActive && (
          <span style={{
            fontSize: 10,
            background: 'var(--accent2)',
            color: '#000',
            borderRadius: 999,
            padding: '1px 6px',
            fontWeight: 700,
            marginLeft: 2,
          }}>
            {matchCount != null ? matchCount.toLocaleString() : '●'}
          </span>
        )}
      </button>

      {open && (
        <div style={{
          position: 'absolute', top: 'calc(100% + 6px)', left: 0, zIndex: 200,
          background: 'var(--bg-card)', border: '1px solid var(--border)',
          borderRadius: 12, padding: 16, minWidth: 340,
          boxShadow: '0 8px 32px rgba(0,0,0,0.4)',
        }}>
          {/* Header */}
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
            <span className="section-title">DATE RANGE FILTER</span>
            <button
              className="btn btn-sm"
              onClick={handleDone}
              style={
                !draftIsApplied
                  ? { borderColor: 'var(--accent)', color: 'var(--accent)', fontWeight: 700 }
                  : {}
              }
            >
              {draftIsApplied ? 'Done' : '✓ Apply'}
            </button>
          </div>

          {dateCols.length === 0 ? (
            <div style={{ color: 'var(--text-muted)', fontSize: 13, textAlign: 'center', padding: 20 }}>
              No date fields found in schema
            </div>
          ) : (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>

              {/* Field selector */}
              <div>
                <label style={{ fontSize: 11, color: 'var(--text-muted)', display: 'block', marginBottom: 4 }}>Date Field</label>
                <select className="input" style={{ width: '100%' }}
                  value={draft.field}
                  onChange={e => setDraft(prev => ({ ...prev, field: e.target.value }))}>
                  <option value="">Select date field</option>
                  {dateCols.map(c => <option key={c} value={c}>{c}</option>)}
                </select>
              </div>

              {/* From / To */}
              <div style={{ display: 'flex', gap: 10 }}>
                <div style={{ flex: 1 }}>
                  <label style={{ fontSize: 11, color: 'var(--text-muted)', display: 'block', marginBottom: 4 }}>From</label>
                  <input type="date" className="input" style={{ width: '100%' }}
                    value={draft.from}
                    onChange={e => setDraft(prev => ({ ...prev, from: e.target.value }))} />
                </div>
                <div style={{ flex: 1 }}>
                  <label style={{ fontSize: 11, color: 'var(--text-muted)', display: 'block', marginBottom: 4 }}>To</label>
                  <input type="date" className="input" style={{ width: '100%' }}
                    value={draft.to}
                    onChange={e => setDraft(prev => ({ ...prev, to: e.target.value }))} />
                </div>
              </div>



              {/* Quick ranges */}
              <div>
                <label style={{ fontSize: 11, color: 'var(--text-muted)', display: 'block', marginBottom: 6 }}>Quick Select</label>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                  {[
                    { label: 'Last 7 days', days: 7 },
                    { label: 'Last 30 days', days: 30 },
                    { label: 'Last 90 days', days: 90 },
                    { label: 'This year', days: 365 },
                  ].map(({ label, days }) => (
                    <button key={label} className="btn btn-sm" onClick={() => applyPreset(days)}>
                      {label}
                    </button>
                  ))}
                </div>
              </div>

              {/* Clear button */}
              {(hasActive || draft.from) && (
                <button className="btn btn-sm btn-danger" onClick={handleClear} style={{ marginTop: 4 }}>
                  <X size={12} /> Clear Date Filter
                </button>
              )}
            </div>
          )}
        </div>
      )}
    </div>
  );
}
