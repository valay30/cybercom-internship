import { useState, useRef } from 'react';
import { Upload, X, CheckCircle, AlertCircle, FileText, Loader } from 'lucide-react';
import axios from 'axios';

const API = 'http://localhost:8000';

const STATE = { IDLE: 'idle', UPLOADING: 'uploading', SUCCESS: 'success', ERROR: 'error' };

export default function UploadCSV({ onUploaded }) {
    const [open, setOpen]       = useState(false);
    const [file, setFile]       = useState(null);
    const [dragging, setDragging] = useState(false);
    const [status, setStatus]   = useState(STATE.IDLE);
    const [result, setResult]   = useState(null);
    const inputRef              = useRef();

    const reset = () => {
        setFile(null);
        setStatus(STATE.IDLE);
        setResult(null);
    };

    const handleClose = () => {
        setOpen(false);
        reset();
    };

    const pickFile = (f) => {
        if (!f) return;
        if (!f.name.endsWith('.csv')) {
            setStatus(STATE.ERROR);
            setResult({ message: 'Only .csv files are accepted.' });
            return;
        }
        setFile(f);
        setStatus(STATE.IDLE);
        setResult(null);
    };

    const handleDrop = (e) => {
        e.preventDefault();
        setDragging(false);
        pickFile(e.dataTransfer.files[0]);
    };

    const handleUpload = async () => {
        if (!file) return;
        setStatus(STATE.UPLOADING);
        setResult(null);

        const formData = new FormData();
        formData.append('csvfile', file);

        try {
            const res = await axios.post(`${API}/upload.php`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
                timeout: 300000,   // 5 min for large files
            });
            setStatus(STATE.SUCCESS);
            setResult(res.data);
            // Notify parent to refresh data after a short delay
            // (consumer needs time to index to Solr)
            setTimeout(() => onUploaded?.(), 3000);
        } catch (err) {
            setStatus(STATE.ERROR);
            setResult({
                message: err.response?.data?.error || err.message || 'Upload failed',
            });
        }
    };

    const formatBytes = (b) => b < 1024 * 1024
        ? `${(b / 1024).toFixed(1)} KB`
        : `${(b / 1024 / 1024).toFixed(1)} MB`;

    return (
        <div style={{ position: 'relative' }}>
            {/* Trigger button */}
            <button
                className="btn"
                onClick={() => setOpen(!open)}
                style={open ? { borderColor: 'var(--accent)', color: 'var(--accent)' } : {}}
                title="Upload a new CSV file to Kafka → Solr"
            >
                <Upload size={13} />
                Upload CSV
            </button>

            {open && (
                <div style={{
                    position: 'absolute', top: 'calc(100% + 6px)', right: 0, zIndex: 300,
                    background: 'var(--bg-card)', border: '1px solid var(--border)',
                    borderRadius: 14, padding: 20, width: 380,
                    boxShadow: '0 12px 40px rgba(0,0,0,0.5)',
                }}>
                    {/* Header */}
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                        <span className="section-title">UPLOAD CSV TO KAFKA</span>
                        <button onClick={handleClose}
                            style={{ background: 'none', border: 'none', color: 'var(--text-dim)', cursor: 'pointer', padding: 4 }}>
                            <X size={14} />
                        </button>
                    </div>

                    {/* Explainer */}
                    <p style={{ fontSize: 11, color: 'var(--text-muted)', marginBottom: 14, lineHeight: 1.6 }}>
                        Your CSV will be streamed to <strong style={{ color: 'var(--accent)' }}>Kafka</strong> and
                        the consumer will automatically index it into <strong style={{ color: 'var(--accent2)' }}>Solr</strong>.
                        Data will appear in the table within seconds.
                    </p>

                    {/* Drop zone */}
                    {status !== STATE.SUCCESS && (
                        <div
                            onClick={() => inputRef.current?.click()}
                            onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
                            onDragLeave={() => setDragging(false)}
                            onDrop={handleDrop}
                            style={{
                                border: `2px dashed ${dragging ? 'var(--accent)' : file ? 'var(--accent2)' : 'var(--border)'}`,
                                borderRadius: 10,
                                padding: '20px 16px',
                                textAlign: 'center',
                                cursor: 'pointer',
                                background: dragging ? 'rgba(99,102,241,0.06)' : file ? 'rgba(34,211,238,0.04)' : 'var(--bg-secondary)',
                                transition: 'all 0.2s',
                                marginBottom: 12,
                            }}
                        >
                            <input
                                ref={inputRef}
                                type="file"
                                accept=".csv"
                                style={{ display: 'none' }}
                                onChange={(e) => pickFile(e.target.files[0])}
                            />
                            {file ? (
                                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 10 }}>
                                    <FileText size={22} color="var(--accent2)" />
                                    <div style={{ textAlign: 'left' }}>
                                        <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)' }}>
                                            {file.name}
                                        </div>
                                        <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>
                                            {formatBytes(file.size)}
                                        </div>
                                    </div>
                                    <button
                                        onClick={(e) => { e.stopPropagation(); reset(); }}
                                        style={{ marginLeft: 'auto', background: 'none', border: 'none', color: 'var(--text-dim)', cursor: 'pointer' }}
                                    >
                                        <X size={13} />
                                    </button>
                                </div>
                            ) : (
                                <>
                                    <Upload size={28} color="var(--text-dim)" style={{ marginBottom: 8 }} />
                                    <div style={{ fontSize: 13, color: 'var(--text-muted)', marginBottom: 4 }}>
                                        Drag & drop a CSV file here
                                    </div>
                                    <div style={{ fontSize: 11, color: 'var(--text-dim)' }}>
                                        or <span style={{ color: 'var(--accent)', textDecoration: 'underline' }}>click to browse</span>
                                    </div>
                                </>
                            )}
                        </div>
                    )}

                    {/* Status feedback */}
                    {status === STATE.UPLOADING && (
                        <div style={{
                            display: 'flex', alignItems: 'center', gap: 10,
                            padding: '10px 14px', borderRadius: 8, marginBottom: 12,
                            background: 'rgba(99,102,241,0.08)', border: '1px solid rgba(99,102,241,0.2)',
                        }}>
                            <Loader size={14} className="spinning" color="var(--accent)" />
                            <div>
                                <div style={{ fontSize: 12, fontWeight: 600, color: 'var(--accent)' }}>Sending to Kafka…</div>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>
                                    Processing <strong>{file?.name}</strong> — this may take a moment for large files
                                </div>
                            </div>
                        </div>
                    )}

                    {status === STATE.SUCCESS && result && (
                        <div style={{
                            padding: '12px 14px', borderRadius: 8, marginBottom: 12,
                            background: 'rgba(16,185,129,0.08)', border: '1px solid rgba(16,185,129,0.25)',
                        }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
                                <CheckCircle size={15} color="#10b981" />
                                <span style={{ fontSize: 13, fontWeight: 700, color: '#10b981' }}>Upload successful!</span>
                            </div>
                            <div style={{ fontSize: 11, color: 'var(--text-muted)', lineHeight: 1.7 }}>
                                <div>📄 <strong>{result.filename}</strong></div>
                                <div>📤 <strong style={{ color: 'var(--text-primary)' }}>{result.rows_sent?.toLocaleString()}</strong> rows sent to Kafka</div>
                                {result.errors > 0 && (
                                    <div style={{ color: '#f59e0b' }}>⚠️ {result.errors} rows skipped (column mismatch)</div>
                                )}
                                <div style={{ marginTop: 6, color: 'var(--accent2)' }}>
                                    ⏳ Data will appear in the table in a few seconds…
                                </div>
                            </div>
                            <button className="btn btn-sm" onClick={reset} style={{ marginTop: 10 }}>
                                Upload another file
                            </button>
                        </div>
                    )}

                    {status === STATE.ERROR && result && (
                        <div style={{
                            display: 'flex', alignItems: 'flex-start', gap: 10,
                            padding: '10px 14px', borderRadius: 8, marginBottom: 12,
                            background: 'rgba(239,68,68,0.08)', border: '1px solid rgba(239,68,68,0.25)',
                        }}>
                            <AlertCircle size={14} color="#ef4444" style={{ flexShrink: 0, marginTop: 1 }} />
                            <div>
                                <div style={{ fontSize: 12, fontWeight: 600, color: '#ef4444', marginBottom: 2 }}>Upload failed</div>
                                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{result.message}</div>
                            </div>
                        </div>
                    )}

                    {/* Upload button */}
                    {status !== STATE.SUCCESS && (
                        <button
                            className="btn btn-primary"
                            onClick={handleUpload}
                            disabled={!file || status === STATE.UPLOADING}
                            style={{ width: '100%', justifyContent: 'center' }}
                        >
                            {status === STATE.UPLOADING
                                ? <><Loader size={13} className="spinning" /> Uploading…</>
                                : <><Upload size={13} /> Send to Kafka</>
                            }
                        </button>
                    )}
                </div>
            )}
        </div>
    );
}
