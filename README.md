# Online Compiler Backend Execution Engine

This project sets up a backend execution engine for an online compiler using Judge0, a Node.js Express proxy, and supporting infrastructure.

## Architecture

The system consists of:

1. **Judge0** - Open source code execution engine that supports 60+ programming languages
2. **Node.js Express Proxy** - Middleware server that forwards requests to Judge0 (can be extended to hide secret test cases)
3. **PostgreSQL** - Database for Judge0 submissions
4. **Redis** - Queue management for Judge0 workers

## Prerequisites

- Docker and Docker Compose
- Node.js (v18+)
- A system with **cgroup v1** support (see Known Limitations below)

## Quick Start

### 1. Configure Environment Variables

Copy the example environment file and set your passwords:

```bash
cp .env.example .env
# Edit .env and set secure passwords for POSTGRES_PASSWORD and REDIS_PASSWORD
```

### 2. Start the Infrastructure

```bash
docker-compose up -d
```

This will start:
- Judge0 server on port 2358
- Judge0 worker for processing submissions
- PostgreSQL database
- Redis for queue management

### 2. Start the Express Proxy Server

```bash
cd server
npm install
npm start
```

The Express server will run on port 3000.

### 3. Test the Setup

```bash
cd server
node test_api.js
```

This sends a "Hello World" Python program to the `/execute` endpoint and verifies the output.

## API Endpoints

### POST /execute

Execute code through the proxy server.

**Request Body:**
```json
{
  "language_id": 71,
  "source_code": "print('Hello World')",
  "stdin": ""
}
```

**Response:**
```json
{
  "stdout": "Hello World\n",
  "stderr": null,
  "status": {
    "id": 3,
    "description": "Accepted"
  },
  "time": "0.01",
  "memory": 1234
}
```

### GET /health

Health check endpoint.

## Language IDs

Common language IDs for Judge0:
- Python 3.8.1: `71`
- JavaScript (Node.js 12.14.0): `63`
- C++ (GCC 9.2.0): `54`
- Java (OpenJDK 13.0.1): `62`
- C (GCC 9.2.0): `50`

Full list available at: `http://localhost:2358/languages`

## Configuration

### Judge0 Configuration (judge0.conf)

The `judge0.conf` file contains configuration for Judge0:
- Database connection settings
- Redis connection settings
- Worker configuration
- Telemetry settings

### Express Server

The Express server can be configured via environment variables:
- `PORT` - Server port (default: 3000)
- `JUDGE0_URL` - Judge0 API URL (default: http://localhost:2358)

## Known Limitations

### cgroup v2 Compatibility

Judge0 uses the `isolate` sandbox which requires **cgroup v1** memory controller. Systems running with unified **cgroup v2** (common in newer Linux distributions and Docker Desktop) will encounter the following error:

```
Failed to create control group /sys/fs/cgroup/memory/box-1/: No such file or directory
```

**Solutions:**

1. **Use a system with cgroup v1 support** - Older Linux distributions or systems configured with hybrid cgroup mode
2. **Configure hybrid cgroup mode** - Add `systemd.unified_cgroup_hierarchy=0` to kernel boot parameters
3. **Use a VM with cgroup v1** - Run the Docker containers inside a VM configured with cgroup v1
4. **Use Judge0 CE (Cloud Edition)** - Use the hosted Judge0 API instead of self-hosted

### Checking cgroup Version

```bash
# Check if cgroup v2 is in use
cat /proc/filesystems | grep cgroup
ls /sys/fs/cgroup/

# If you see /sys/fs/cgroup/memory/, you have cgroup v1 support
# If you only see unified cgroup v2 controllers, you need to configure hybrid mode
```

## Project Structure

```
.
├── docker-compose.yml    # Docker Compose configuration for Judge0 infrastructure
├── judge0.conf           # Judge0 configuration file
├── server/
│   ├── package.json      # Node.js dependencies
│   ├── index.js          # Express proxy server
│   └── test_api.js       # Test script for verification
└── README.md             # This file
```

## Development

### Extending the Proxy

The Express proxy server (`server/index.js`) can be extended to:
- Add authentication
- Hide secret test cases from the frontend
- Add rate limiting
- Log submissions for analytics
- Add custom validation

### Adding New Languages

Judge0 supports 60+ languages out of the box. Check available languages:

```bash
curl http://localhost:2358/languages
```

## Troubleshooting

### Containers not starting

```bash
# Check container status
docker-compose ps

# View logs
docker-compose logs server
docker-compose logs worker
docker-compose logs db
docker-compose logs redis
```

### Database issues

```bash
# Reset the database
docker-compose down -v
docker-compose up -d
```

### Judge0 Internal Error (status 13)

This usually indicates a cgroup v1/v2 compatibility issue. See the "Known Limitations" section above.

## License

This project uses Judge0 which is licensed under the GNU General Public License v3.0.
