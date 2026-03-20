# Kafka → Solr → React Reporting Dashboard

## Project Structure

```
kafka-solr-project/
├── docker-compose.yml        # All services auto-configured
├── producer.php              # Reads CSV → sends to Kafka
├── consumer.php              # Reads Kafka → indexes to Solr
├── composer.json             # PHP dependencies
├── dead_letter.log           # Failed docs (auto created)
├── csvfiles/                 # Put your CSV files here
│   └── *.csv
├── backend/                  # PHP API (served by Docker)
│   ├── search.php            # Main search endpoint
│   ├── schema.php            # Returns field schema
│   ├── views.php             # Saved views CRUD
│   └── .htaccess
└── frontend/                 # React app
    ├── package.json
    ├── public/index.html
    └── src/
        ├── index.js
        ├── App.jsx
        ├── App.css
        └── components/
            ├── DataTable.jsx
            ├── FilterBuilder.jsx
            ├── ChartRenderer.jsx
            ├── ColumnSelector.jsx
            ├── DateRangeFilter.jsx
            └── SavedViews.jsx
```

---

## Prerequisites

- Docker Desktop running
- PHP installed (for producer/consumer)
- Node.js 18+ installed (for React frontend)
- composer installed

---

## Step 1 — Install PHP dependencies

```bash
composer install
```

---

## Step 2 — Add your CSV files

Copy all your CSV files into the `csvfiles/` folder.

---

## Step 3 — Start all Docker services

```bash
docker-compose up -d
```

This automatically:
- Starts Zookeeper
- Starts Kafka broker
- Creates Kafka topic `kafka-solr-csvdata` with **4 partitions**
- Creates Solr core `csvcore`
- Starts Kafka UI at http://localhost:8080
- Starts PHP API at http://localhost:8000

Wait ~30 seconds for all services to be ready.

---

## Step 4 — Verify everything is running

```bash
docker-compose ps
```

All containers should show "Up":
```
zookeeper    Up
kafka        Up
kafka-setup  Exit 0   ← normal, just creates topic and exits
solr         Up
kafbat-ui    Up
php-api      Up
```

Verify topic has 4 partitions:
```bash
docker exec kafka kafka-topics --describe --bootstrap-server localhost:9092 --topic kafka-solr-csvdata
```

---

## Step 5 — Start 4 consumer processes

Open 4 separate terminal windows:

```bash
# Terminal 1
php consumer.php 0

# Terminal 2
php consumer.php 1

# Terminal 3
php consumer.php 2

# Terminal 4
php consumer.php 3
```

Wait for all 4 to print:
```
Listening on topic: kafka-solr-csvdata (group: solr-indexer-group)
```

---

## Step 6 — Run the producer

```bash
php producer.php
```

Expected output:
```
========================================
  KAFKA PRODUCER - CSV INDEXER
========================================

Found 16 CSV files

[1/16] Processing: AF.csv
  Sent batch of 1000
  Sent batch of 1000
  Finished — 2340 rows

...

Sending sentinel signals to 4 partitions...

========================================
  DONE — TOTAL ROWS SENT: 740659
========================================
```

---

## Step 7 — Verify data in Solr

```bash
curl "http://localhost:8983/solr/csvcore/select?q=*:*&rows=0"
```

`numFound` should match total rows sent.

---

## Step 8 — Start the React frontend

```bash
cd frontend
npm install
npm start
```

Opens at **http://localhost:3000**

---

## URLs

| Service       | URL                          |
|---------------|------------------------------|
| React App     | http://localhost:3000        |
| PHP API       | http://localhost:8000        |
| Solr Admin    | http://localhost:8983        |
| Kafka UI      | http://localhost:8080        |

---

## Features

### Data Table
- View all indexed documents
- Sort by any column (click header)
- Pagination with page numbers
- Export to CSV

### Filters
- Equals filter
- Contains (text search)
- Range filter (from–to)
- Multiple filters with AND logic
- Clear all filters

### Date Range Filter
- Select any date field
- From/To date picker
- Quick ranges: Last 7/30/90 days, This year

### Column Selector
- Show/hide any column
- Search columns by name
- Select all / none

### Charts
- Bar chart
- Line chart
- Pie chart
- Dynamic X/Y axis selection
- Summary stats panel
- Top values bar chart

### Saved Views
- Save current columns + filters + sort
- Load saved view with one click
- Delete saved views
- Stored in backend file + localStorage fallback

---

## Check lag (are consumers keeping up?)

```bash
docker exec kafka kafka-consumer-groups --bootstrap-server localhost:9092 --describe --group solr-indexer-group
```

LAG = 0 means all messages consumed.

---

## Reset everything (start fresh)

```bash
# Stop containers
docker-compose down

# Delete topic and recreate
docker exec kafka kafka-topics --delete --bootstrap-server localhost:9092 --topic kafka-solr-csvdata
docker exec kafka kafka-topics --create --bootstrap-server localhost:9092 --topic kafka-solr-csvdata --partitions 4 --replication-factor 1

# Clear Solr
curl "http://localhost:8983/solr/csvcore/update?commit=true" \
  -H "Content-Type: application/json" \
  -d '{"delete":{"query":"*:*"}}'

# Start fresh
docker-compose up -d
php consumer.php 0
php consumer.php 1
php consumer.php 2
php consumer.php 3
php producer.php
```

---

## Safe stop (data preserved)

```bash
docker-compose stop    # keeps all data
docker-compose start   # resumes, data still there
```

**NEVER use** `docker-compose down -v` unless you want to wipe all data.

---

## Troubleshooting

### PHP cannot connect to Kafka
Make sure broker is set to `localhost:29092` in producer.php and consumer.php.

### Solr shows fewer docs than expected
Check `dead_letter.log` for failed documents.

### React cannot connect to API
Make sure `php-api` container is running: `docker-compose ps`

### Topic not found
```bash
docker exec kafka kafka-topics --create --if-not-exists \
  --bootstrap-server localhost:9092 \
  --topic kafka-solr-csvdata \
  --partitions 4 \
  --replication-factor 1
```
