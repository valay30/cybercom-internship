import { useState, useRef, useMemo } from 'react';
import {
  BarChart, Bar, LineChart, Line, PieChart, Pie, Cell, AreaChart, Area,
  XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer,
  ComposedChart
} from 'recharts';
import html2canvas from 'html2canvas';
import { Download, TrendingUp, BarChart3, PieChart as PieIcon, Layers, Calendar, ChevronRight } from 'lucide-react';

import { formatFieldName } from '../utils/fieldFormatter';

const COLORS = [
  '#6366f1', '#22d3ee', '#10b981', '#f59e0b', '#ef4444', 
  '#a78bfa', '#34d399', '#fb923c', '#ec4899', '#8b5cf6'
];

const CHART_TYPES = [
  { id: 'Bar',   icon: BarChart3, label: 'Bar' },
  { id: 'Line',  icon: TrendingUp, label: 'Trend' },
  { id: 'Area',  icon: Layers,     label: 'Area' },
  { id: 'Pie',   icon: PieIcon,   label: 'Distro' },
];

export default function ChartRenderer({ data, columns, total, onFilter }) {
  const chartRef = useRef(null);
  
  // -- State --
  const [chartType, setChartType]   = useState('Bar');
  const [xAxis, setXAxis]           = useState('');
  const [yAxes, setYAxes]           = useState([]); // Multi-metrics support
  const [groupBy, setGroupBy]       = useState(''); // Multi-series support
  const [timeStep, setTimeStep]     = useState('Day'); // For date-trend
  const [exporting, setExporting]   = useState(false);
  const [limit, setLimit]           = useState(15);

  // -- Column categorization --
  const numericCols = useMemo(() => columns.filter(c => c.endsWith('_i') || c.endsWith('_f')), [columns]);
  const dateCols    = useMemo(() => columns.filter(c => c.endsWith('_dt') || c.toLowerCase().includes('date') || c.toLowerCase().includes('time')), [columns]);
  const stringCols  = useMemo(() => columns.filter(c => !numericCols.includes(c) && !dateCols.includes(c)), [columns, numericCols, dateCols]);

  // -- Defaults --
  const activeX = xAxis || dateCols[0] || stringCols[0] || columns[0] || '';
  const activeYs = yAxes.length > 0 ? yAxes : (numericCols[0] ? [numericCols[0]] : []);
  const isDateX  = dateCols.includes(activeX);

  // -- Data Processing --
  const aggregated = useMemo(() => {
    if (!activeX || !data.length) return [];

    const map = {};
    const seriesKeys = new Set();

    data.forEach(row => {
      let xVal = row[activeX];
      
      // Handle date grouping
      if (isDateX && xVal) {
        const d = new Date(xVal);
        if (!isNaN(d)) {
          if (timeStep === 'Year')  xVal = `${d.getFullYear()}`;
          else if (timeStep === 'Month') xVal = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
          else if (timeStep === 'Week') {
            const first = d.getDate() - d.getDay();
            const week = new Date(d.setDate(first));
            xVal = `W${week.toISOString().split('T')[0]}`;
          } else {
            xVal = d.toISOString().split('T')[0];
          }
        }
      }

      const xKey = String(xVal ?? 'Unknown').slice(0, 30);
      if (!map[xKey]) {
        map[xKey] = { name: xKey, _raw: xVal, count: 0 };
        activeYs.forEach(y => map[xKey][y] = 0);
      }

      map[xKey].count++;
      
      if (groupBy && row[groupBy]) {
        const gKey = String(row[groupBy]);
        seriesKeys.add(gKey);
        map[xKey][gKey] = (map[xKey][gKey] || 0) + 1;
      }

      activeYs.forEach(y => {
        map[xKey][y] += Number(row[y] ?? 0);
      });
    });

    let result = Object.values(map);

    // Sorting
    if (isDateX) {
      result.sort((a, b) => new Set([a._raw, b._raw]).size === 1 ? 0 : (a._raw > b._raw ? 1 : -1));
    } else {
      result.sort((a, b) => b.count - a.count);
    }

    return {
      data: result.slice(0, limit),
      series: Array.from(seriesKeys).slice(0, 5) // Limit to top 5 series for clarity
    };
  }, [data, activeX, activeYs, groupBy, timeStep, isDateX, limit]);

  const exportChart = async () => {
    if (!chartRef.current) return;
    setExporting(true);
    setTimeout(async () => {
      try {
        const canvas = await html2canvas(chartRef.current, {
          backgroundColor: '#16161f',
          scale: 2,
          logging: false,
          ignoreElements: (el) => el.classList.contains('chart-controls') || el.classList.contains('recharts-tooltip-wrapper')
        });
        const link = document.createElement('a');
        link.download = `chart-${activeX}-${new Date().getTime()}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
      } finally {
        setExporting(false);
      }
    }, 100);
  };

  const handlePointClick = (payload) => {
    if (onFilter && activeX && payload?.name) {
      onFilter(activeX, payload.name);
    }
  };

  const CustomTooltip = ({ active, payload, label }) => {
    if (!active || !payload?.length) return null;
    return (
      <div style={{ 
        background: 'var(--bg-card)', border: '1px solid var(--border)', 
        borderRadius: 12, padding: '12px 16px', boxShadow: '0 10px 30px rgba(0,0,0,0.5)',
        minWidth: 180
      }}>
        <div style={{ fontWeight: 800, marginBottom: 10, color: 'var(--text-primary)', borderBottom: '1px solid var(--border)', pb: 6 }}>
          {label}
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
          {payload.map((p, i) => (
            <div key={i} style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                <div style={{ width: 8, height: 8, borderRadius: '50%', background: p.color }} />
                <span style={{ fontSize: 11, color: 'var(--text-muted)' }}>{formatFieldName(p.name)}:</span>
              </div>
              <span style={{ fontFamily: 'var(--mono)', fontSize: 12, fontWeight: 700, color: 'var(--text-primary)' }}>
                {p.value?.toLocaleString()}
              </span>
            </div>
          ))}
        </div>
        {onFilter && (
          <div style={{ marginTop: 12, pt: 8, borderTop: '1px dotted var(--border)', display: 'flex', alignItems: 'center', gap: 4, color: 'var(--accent2)', fontSize: 10 }}>
            <ChevronRight size={10} /> Click segment to drill-down
          </div>
        )}
      </div>
    );
  };

  return (
    <div className="advanced-charts">
      {/* Controls Bar */}
      <div className="chart-controls card" style={{ padding: '16px 20px', marginBottom: 20 }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 16 }}>
          
          <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
            <div className="btn-group">
              {CHART_TYPES.map(t => (
                <button 
                  key={t.id} 
                  className={`btn-tab ${chartType === t.id ? 'active' : ''}`}
                  onClick={() => setChartType(t.id)}
                >
                  <t.icon size={14} />
                  <span>{t.label}</span>
                </button>
              ))}
            </div>
          </div>

          <div style={{ display: 'flex', gap: 8, alignItems: 'center' }}>
            <Calendar size={14} color="var(--text-dim)" />
            <select className="input input-sm" value={activeX} onChange={e => setXAxis(e.target.value)}>
              <option disabled>Dimension (X Axis)</option>
              <optgroup label="Dates">
                {dateCols.map(c => <option key={c} value={c}>{formatFieldName(c)}</option>)}
              </optgroup>
              <optgroup label="Categories">
                {stringCols.map(c => <option key={c} value={c}>{formatFieldName(c)}</option>)}
              </optgroup>
            </select>

            {isDateX && (
              <select className="input input-sm" value={timeStep} onChange={e => setTimeStep(e.target.value)} style={{ borderColor: 'var(--accent)' }}>
                <option value="Day">Group by Day</option>
                <option value="Week">Group by Week</option>
                <option value="Month">Group by Month</option>
                <option value="Year">Group by Year</option>
              </select>
            )}

            <div style={{ width: 1, height: 20, background: 'var(--border)', margin: '0 4px' }} />

            <select className="input input-sm" value={groupBy} onChange={e => setGroupBy(e.target.value)}>
              <option value="">No Series Grouping</option>
              {stringCols.slice(0, 10).map(c => <option key={c} value={c}>Grouped by {formatFieldName(c)}</option>)}
            </select>
          </div>

          <div style={{ display: 'flex', gap: 8 }}>
            <button className="btn btn-sm btn-outline" onClick={exportChart} disabled={exporting}>
              <Download size={13} />
              Export
            </button>
          </div>
        </div>

        {/* Multi-Y Selection Chips */}
        {!groupBy && (
          <div style={{ marginTop: 12, pt: 12, borderTop: '1px solid var(--border-light)', display: 'flex', alignItems: 'center', gap: 10 }}>
            <span style={{ fontSize: 11, color: 'var(--text-dim)', fontWeight: 600 }}>METRICS:</span>
            <div style={{ display: 'flex', gap: 6, flexWrap: 'wrap' }}>
              <button 
                className={`tag-btn ${yAxes.length === 0 ? 'active' : ''}`}
                onClick={() => setYAxes([])}
              >
                Record Count
              </button>
              {numericCols.map(c => (
                <button 
                  key={c}
                  className={`tag-btn ${yAxes.includes(c) ? 'active' : ''}`}
                  onClick={() => {
                    const next = yAxes.includes(c) ? yAxes.filter(x => x !== c) : [...yAxes, c];
                    setYAxes(next.slice(0, 3)); // Max 3
                  }}
                >
                  {formatFieldName(c)}
                </button>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* Main Analysis Grid */}
      <div ref={chartRef} className="chart-grid-v2">
        <div className="chart-main card">
          <div style={{ padding: '16px 20px', borderBottom: '1px solid var(--border-light)', display: 'flex', justifyContent: 'space-between' }}>
            <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)' }}>
              {isDateX ? 'Trend Analysis' : 'Comparison Analysis'}: {formatFieldName(activeX)}
            </div>
            <div style={{ fontSize: 11, color: 'var(--text-dim)' }}>
              Analytics on <strong>{data.length.toLocaleString()}</strong> rows
            </div>
          </div>

          <div style={{ padding: 24 }}>
            {aggregated.data.length === 0 ? (
              <div className="empty-state" style={{ height: 360 }}>📊 Select axes to begin analysis</div>
            ) : (
                <ResponsiveContainer width="100%" height={400}>
                {(() => {
                  const props = { 
                    data: aggregated.data, 
                    margin: { top: 10, right: 30, left: 0, bottom: 40 },
                    onClick: (e) => e && handlePointClick(e.activePayload?.[0]?.payload)
                  };
                  
                  const shared = (
                    <>
                      <CartesianGrid strokeDasharray="3 3" stroke="var(--border)" vertical={false} />
                      <XAxis 
                        dataKey="name" 
                        tick={{ fill: 'var(--text-muted)', fontSize: 10 }}
                        angle={-30} 
                        textAnchor="end" 
                        interval={Math.ceil(aggregated.data.length / 15)}
                      />
                      <YAxis tick={{ fill: 'var(--text-muted)', fontSize: 10 }} />
                      <Tooltip content={<CustomTooltip />} />
                      <Legend iconType="circle" wrapperStyle={{ paddingTop: 20, fontSize: 11 }} />
                    </>
                  );

                  // Decide if we use built-in count or series or Y-axes
                  const dataKeys = groupBy ? aggregated.series : (yAxes.length > 0 ? yAxes : ['count']);

                  if (chartType === 'Bar') {
                    return (
                      <BarChart {...props}>
                        {shared}
                        {dataKeys.map((key, i) => (
                          <Bar 
                            key={key} 
                            dataKey={key} 
                            name={formatFieldName(key)}
                            fill={COLORS[i % COLORS.length]} 
                            radius={[4, 4, 0, 0]} 
                            stackId={groupBy ? 'a' : undefined}
                            style={{ cursor: 'pointer' }}
                          />
                        ))}
                      </BarChart>
                    );
                  }
                  
                  if (chartType === 'Line') {
                    return (
                      <LineChart {...props}>
                        {shared}
                        {dataKeys.map((key, i) => (
                          <Line 
                            key={key} 
                            type="monotone" 
                            dataKey={key} 
                            name={formatFieldName(key)}
                            stroke={COLORS[i % COLORS.length]} 
                            strokeWidth={3} 
                            dot={{ r: 4, strokeWidth: 2, fill: 'var(--bg-card)' }}
                            activeDot={{ r: 6 }}
                            style={{ cursor: 'pointer' }}
                          />
                        ))}
                      </LineChart>
                    );
                  }

                  if (chartType === 'Area') {
                    return (
                      <AreaChart {...props}>
                        {shared}
                        {dataKeys.map((key, i) => (
                          <Area 
                            key={key} 
                            type="monotone" 
                            dataKey={key} 
                            name={formatFieldName(key)}
                            stroke={COLORS[i % COLORS.length]} 
                            fill={COLORS[i % COLORS.length]} 
                            fillOpacity={0.15}
                            strokeWidth={2}
                            stackId="1"
                            style={{ cursor: 'pointer' }}
                          />
                        ))}
                      </AreaChart>
                    );
                  }

                  return (
                    <PieChart>
                      <Pie 
                        data={aggregated.data} 
                        dataKey={dataKeys[0]} 
                        nameKey="name"
                        cx="50%" cy="50%" 
                        innerRadius={80}
                        outerRadius={140} 
                        paddingAngle={2}
                        label={({ name, percent }) => `${name.slice(0, 10)} ${(percent * 100).toFixed(0)}%`}
                        onClick={(e) => handlePointClick(e)}
                        style={{ cursor: 'pointer' }}
                      >
                        {aggregated.data.map((_, i) => <Cell key={i} fill={COLORS[i % COLORS.length]} />)}
                      </Pie>
                      <Tooltip content={<CustomTooltip />} />
                    </PieChart>
                  );
                })()}
                </ResponsiveContainer>
            )}
          </div>
        </div>

        {/* Sidebar Insights */}
        <div className="chart-sidebar">
          <div className="card" style={{ padding: 20 }}>
            <div className="section-title" style={{ marginBottom: 16, display: 'flex', alignItems: 'center', gap: 8 }}>
                <TrendingUp size={16} color="var(--accent)" />
                Key Metrics
            </div>
            <div style={{ display: 'grid', gap: 12 }}>
                {[
                  { label: 'Total Volume', value: total.toLocaleString(), sub: 'All matches' },
                  { label: 'Sample Population', value: data.length.toLocaleString(), sub: 'Loaded rows' },
                  { label: 'Unique Groups', value: aggregated.data.length.toLocaleString(), sub: `On ${formatFieldName(activeX)}` },
                ].map((m, i) => (
                  <div key={i} style={{ padding: '12px 16px', background: 'var(--bg-secondary)', borderRadius: 10 }}>
                    <div style={{ fontSize: 11, color: 'var(--text-dim)', marginBottom: 4 }}>{m.label}</div>
                    <div style={{ fontSize: 20, fontWeight: 900, color: 'var(--text-primary)' }}>{m.value}</div>
                    <div style={{ fontSize: 10, color: 'var(--accent2)' }}>{m.sub}</div>
                  </div>
                ))}
            </div>
            
            <div style={{ marginTop: 20, pt: 20, borderTop: '1px solid var(--border)' }}>
               <div style={{ fontSize: 11, color: 'var(--text-dim)', fontStyle: 'italic', lineHeight: 1.5 }}>
                 ℹ️ Tip: Click any data point to automatically apply a filter for that category.
               </div>
            </div>
          </div>
        </div>
      </div>

      <style>{`
        .chart-grid-v2 {
          display: grid;
          grid-template-columns: 1fr 300px;
          gap: 20px;
        }
        .btn-group {
          display: flex;
          background: var(--bg-hover);
          padding: 3px;
          border-radius: 10px;
          border: 1px solid var(--border);
        }
        .btn-tab {
          display: flex;
          align-items: center;
          gap: 6px;
          padding: 6px 14px;
          border: none;
          background: transparent;
          color: var(--text-dim);
          font-size: 11px;
          font-weight: 700;
          cursor: pointer;
          border-radius: 8px;
          transition: all 0.2s;
        }
        .btn-tab.active {
          background: var(--accent);
          color: #fff;
          box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .tag-btn {
          padding: 4px 10px;
          background: var(--bg-secondary);
          border: 1px solid var(--border);
          border-radius: 6px;
          color: var(--text-dim);
          font-size: 10px;
          cursor: pointer;
          font-weight: 500;
          transition: all 0.2s;
        }
        .tag-btn.active {
          background: var(--accent2);
          border-color: var(--accent2);
          color: #000;
          font-weight: 700;
        }
        .tag-btn:hover:not(.active) {
          border-color: var(--accent);
          color: var(--accent);
        }
        .input-sm {
          padding: 4px 8px;
          font-size: 11px;
          height: 30px;
          min-height: 30px;
        }
        @media (max-width: 1024px) {
          .chart-grid-v2 { grid-template-columns: 1fr; }
        }
      `}</style>
    </div>
  );
}
