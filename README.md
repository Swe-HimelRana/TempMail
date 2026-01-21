# MyTempMail

A self-hosted, lightweight temporary email service that connects to your existing IMAP accounts.

## Features
- 📬 **IMAP Integration**: Connects to any standard IMAP provider.
- 🐳 **Dockerized**: Easy deployment with Docker Compose.
- 📱 **Responsive**: Modern, mobile-friendly web interface.
- 🔍 **Search & Manage**: Search, paginate, and switch between active email sessions.
- 🔒 **PIN Protection**: Simple PIN-based access control.

## Quick Start

### 1. Configure
Create a `tempmail.yml` file (copy `tempmail.sample.yml`) and configure your IMAP accounts:
```yaml
app:
  pin: "12345" # Set your access PIN

accounts:
  - email: "catchall@yourdomain.com"
    password: "yourpassword"
    host: "imap.yourprovider.com"
    port: 993
    encryption: "ssl"
    domains: ["yourdomain.com"]
```

### 2. Run with Docker
Start the application container:
```bash
docker-compose up -d --build
```

### 3. Access
Open your browser at [http://localhost:8080](http://localhost:8080) and enter the PIN configured in your `tempmail.yml`.

## Management
- **Persistent Data**: Emails are stored in the `./data` directory (SQLite).
- **Configuration**: Edit `tempmail.yml` and restart the container to apply changes (`docker-compose restart`).
- **Update**: Rebuild the image: `docker-compose build --no-cache`.

## License
This project is licensed under the Non-Commercial Source-Available License - see the [LICENSE](LICENSE) file for details.
