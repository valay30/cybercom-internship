# ⚡ DataFlow: Full-Stack Reporting & Analytics Dashboard

A high-performance reporting system built to handle large datasets using a real-time data pipeline (CSV → Kafka → Solr) and a customized React-based analytical frontend.

---

## 🏗 System Architecture

The application follows a modern, decoupled data engineering architecture:

1.  **Ingestion Layer**: `producer.php` and `upload.php` stream CSV rows to Kafka.
2.  **Streaming Layer**: **Apache Kafka** handles topic partitioning (4 partitions) for high-throughput messaging.
3.  **Storage & Search Layer**: **Apache Solr** indexes data using dynamic field mapping for lightning-fast querying across hundreds of columns.
4.  **API Layer**: **PHP (Apache)** acts as a secure proxy, normalizing frontend filter requests into optimized Solr Lucene queries.
5.  **Presentation Layer**: **React JS** provides a premium, interactive analytical dashboard.

---

## 📂 Project Structure

```text
kafka-solr-project/
├── backend/            # PHP API Layer (Apache)
│   ├── search.php      # Main Solr query builder & proxy
│   ├── upload.php      # CSV to Kafka streaming endpoint
│   └── views.php       # Saved view persistence logic
├── frontend/           # React Dashboard
│   ├── src/
│   │   ├── components/ # Advanced Charts, Table, Filter Builder
│   │   ├── App.jsx     # Main logic & State management
│   │   └── utils/      # Field formatters & Helpers
│   └── public/
├── csvfiles/           # Local storage for data files
├── consumer.php        # Kafka to Solr indexing worker (Background)
├── producer.php        # Bulk CSV to Kafka importer (CLI)
├── docker-compose.yml  # Kafka, Solr, & PHP Infrastructure
└── Dockerfile.php      # Custom PHP image with rdkafka extension
```

---

## 🚀 Key Features

### 1. Advanced Data Table
*   **Dynamic Columns**: Toggle visibility and reorder columns with drag-and-drop.
*   **Persistent Layout**: Column widths are remembered across sessions and saved within "Views".
*   **Performance**: Optimized for large data loads with pagination and sorting.

### 2. Multi-Logic Filter Builder
*   **Grouped Logic**: Build complex nested queries using per-row **AND/OR** logic.
*   **Operator Support**: Ranges, exact matches, text containment (starts with, contains), and numeric comparisons (GT/LT), and date pickers.
*   **Confirmation Mode**: Filters are buffered locally and only applied to the dashboard when you click "Apply," reducing unnecessary API calls.

### 3. Integrated Analytics (Advanced Charts)
*   **Date-Trend Analysis**: Automatically converts date fields into time-series charts grouped by **Day, Week, Month, or Year**.
*   **Multi-Series & Stacking**: Compare multiple metrics side-by-side or stack categories for composition analysis.
*   **Interactive Drill-Down**: Click any bar or pie slice to instantly filter the entire dashboard to that segment.
*   **Export**: Built-in support for exporting high-resolution PNGs of your analytical views.

### 4. Saved Views (Snapshots)
*   Save your exact dashboard state—including active filters, column visibility, sorting, and widths—into personalized "Views" stored on the server.

---

## ⚙️ Setup & Installation

### Prerequisites
*   [Docker Desktop](https://www.docker.com/products/docker-desktop/)
*   [Node.js](https://nodejs.org/) (for local frontend development)

### 1. Infrastructure (Docker)
Start the Kafka, Solr, and PHP backend services:
```bash
docker-compose up -d --build
```
*   **Solr Dashboard**: [http://localhost:8983](http://localhost:8983)
*   **Kafka UI**: [http://localhost:8080](http://localhost:8080)
*   **PHP API**: [http://localhost:8000](http://localhost:8000)

### 2. Frontend (React)
```bash
cd frontend
npm install
npm start
```
The dashboard will be available at [http://localhost:3000](http://localhost:3000).

---

## 📊 Running the Data Pipeline

### Step 1: Start the Consumer
The consumer indexes data from Kafka into Solr. Run this locally (or inside Docker):
```bash
php consumer.php
```

### Step 2: Import Data (Optional)
If you have CSV files in `/csvfiles`, you can bulk-import them:
```bash
php producer.php
```

### Step 3: Direct Upload
Alternatively, use the **"Upload CSV"** button in the top right of the dashboard to stream files directly through the Kafka pipeline.

---

## 🔒 Security & Architecture Decisions
*   **Backend Query Normalization**: Filter logic moved to the PHP backend to prevent Lucene Injection and improve security.
*   **Kafka Partitioning**: The `solr-data` topic is pre-configured with **4 partitions**, allowing for horizontal scaling of consumers.
*   **Dynamic Field Mapping**: Uses Solr's `_s`, `_dt`, `_i`, and `_f` suffixes to handle dynamic data types without predefined schemas.
