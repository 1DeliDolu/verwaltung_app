# Demo Mode

This setup is for a probe/demo environment only.

## What it does

- uses MailHog instead of a real mail server
- generates a dedicated demo env file
- generates demo credentials for Samba and sample mailboxes
- prepares department file shares
- starts the internal mail and file stack in demo mode

## Start

```bash
infra/scripts/start-demo-services.sh
```

## Notes

- MailHog captures SMTP mail on port `1025`
- MailHog web UI runs on port `8025`
- no real external mail delivery is expected in demo mode
- replace `.demo` naming with your preferred lab domain if needed
