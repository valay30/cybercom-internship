import { useState, useEffect } from 'react';
import { BookmarkPlus, Trash2, Play } from 'lucide-react';
import axios from 'axios';

const API = 'http://localhost:8000';

export default function SavedViews({ currentFilters, currentColumns, currentSort, currentColWidths = {}, onLoad }) {
  const [views, setViews]     = useState([]);
  const [name, setName]       = useState('');
  const [saving, setSaving]   = useState(false);
  const [loading, setLoading] = useState(false);

  const fetchViews = async () => {
    setLoading(true);
    try {
      const res = await axios.get(`${API}/views.php`);
      setViews(res.data);
    } catch {
      // Views stored locally as fallback
      const local = JSON.parse(localStorage.getItem('saved_views') || '[]');
      setViews(local);
    }
    setLoading(false);
  };

  useEffect(() => { fetchViews(); }, []);

  const saveView = async () => {
    if (!name.trim()) return;
    setSaving(true);
    const view = {
      id:          Date.now().toString(),
      name:        name.trim(),
      columns:     currentColumns,
      filters:     currentFilters,
      sort:        currentSort,
      colWidths:   currentColWidths,
      created_at:  new Date().toLocaleString(),
    };
    try {
      await axios.post(`${API}/views.php`, view);
    } catch {
      // Fallback to localStorage
      const local = JSON.parse(localStorage.getItem('saved_views') || '[]');
      local.push(view);
      localStorage.setItem('saved_views', JSON.stringify(local));
    }
    setName('');
    setSaving(false);
    fetchViews();
  };

  const deleteView = async (id) => {
    try {
      await axios.delete(`${API}/views.php?id=${id}`);
    } catch {
      const local = JSON.parse(localStorage.getItem('saved_views') || '[]');
      localStorage.setItem('saved_views', JSON.stringify(local.filter(v => v.id !== id)));
    }
    fetchViews();
  };

  return (
    <div>
      {/* Save current view */}
      <div className="card" style={{ padding: 20, marginBottom: 20 }}>
        <div className="section-title" style={{ marginBottom: 12 }}>Save Current View</div>
        <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>
          <input className="input" placeholder="View name..." value={name}
            onChange={e => setName(e.target.value)}
            onKeyDown={e => e.key === 'Enter' && saveView()}
            style={{ flex: 1, maxWidth: 320 }} />
          <button className="btn btn-primary" onClick={saveView} disabled={saving || !name.trim()}>
            <BookmarkPlus size={14} />
            {saving ? 'Saving...' : 'Save View'}
          </button>
        </div>
        <div style={{ marginTop: 10, display: 'flex', gap: 8, flexWrap: 'wrap' }}>
          <span style={{ fontSize: 11, color: 'var(--text-muted)' }}>Current snapshot:</span>
          <span className="tag">{currentColumns.length} columns</span>
          <span className="tag">{currentFilters.length} filters</span>
          {Object.keys(currentColWidths).length > 0 && (
            <span className="tag">↔ {Object.keys(currentColWidths).length} widths</span>
          )}
          {currentSort && <span className="tag">sort: {currentSort}</span>}
        </div>
      </div>

      {/* Views list */}
      <div className="section-title">Saved Views ({views.length})</div>

      {loading ? (
        <div style={{ color: 'var(--text-muted)', fontSize: 13, padding: 20 }}>Loading...</div>
      ) : views.length === 0 ? (
        <div className="empty-state">
          <div className="empty-state-icon">🔖</div>
          <h3>No saved views yet</h3>
          <p>Configure your columns, filters and sort, then save it as a view above</p>
        </div>
      ) : (
        <div className="views-grid">
          {views.map(view => (
            <div key={view.id} className="view-card">
              <div className="view-card-name">{view.name}</div>
              <div className="view-card-meta">{view.created_at}</div>
              <div className="view-card-tags">
                <span className="tag">{view.columns?.length || 0} cols</span>
                <span className="tag">{view.filters?.length || 0} filters</span>
                {view.sort && <span className="tag">↕ {view.sort}</span>}
              </div>
              <div style={{ fontSize: 11, color: 'var(--text-dim)', marginBottom: 10, fontFamily: 'var(--mono)' }}>
                {view.columns?.slice(0, 4).join(', ')}{view.columns?.length > 4 ? `...+${view.columns.length - 4}` : ''}
              </div>
              <div style={{ display: 'flex', gap: 8 }}>
                <button className="btn btn-sm btn-primary" onClick={() => onLoad(view)} style={{ flex: 1, justifyContent: 'center' }}>
                  <Play size={12} /> Load View
                </button>
                <button className="btn btn-sm btn-danger" onClick={() => deleteView(view.id)}>
                  <Trash2 size={12} />
                </button>
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
