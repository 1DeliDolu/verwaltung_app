# Demo Mode

This setup is for a probe/demo environment only.

## What it does

- generates demo credentials
- generates a dedicated demo env file
- creates self-signed TLS certificates
- prepares department file shares
- starts the internal mail and file stack in demo mode

## Start

```bash
infra/scripts/start-demo-services.sh
```

## Notes

- self-signed certificates are expected in demo mode
- mail delivery should be considered internal/demo only
- replace `.demo` naming with your preferred lab domain if needed
