# Production Deployment Checklist

## 1. Host Preparation

- Provision a Linux host with a static public IP.
- Install Docker Engine and Docker Compose plugin.
- Synchronize time with NTP.
- Reserve enough disk for `/var/mail` and department shares.
- Restrict SSH access to admins only.

## 2. DNS Preparation

- Create an `A` record for `mail.<your-domain>`.
- Point the domain `MX` record to `mail.<your-domain>`.
- Ensure reverse DNS (`PTR`) for the public IP resolves back to `mail.<your-domain>`.
- Add SPF, DKIM and DMARC TXT records before production mail traffic.
- Keep TTL low during rollout, then increase after validation.

## 3. TLS Preparation

- Obtain a valid certificate for `mail.<your-domain>`.
- Prefer Let's Encrypt or another public CA for internet-facing mail.
- Mount the full certificate chain and private key into the mail container.
- Automate certificate renewal and test renewal before go-live.

## 4. Firewall Rules

- Allow inbound TCP `25` for SMTP.
- Allow inbound TCP `465` and `587` for authenticated submission.
- Allow inbound TCP `143` and `993` for IMAP and IMAPS if clients need mailbox access.
- Allow inbound TCP `445` only from trusted internal networks or VPN for Samba.
- Block admin endpoints from the public internet unless required.

## 5. Mail Server Setup

- Generate `.env.internal-services` and `postfix-accounts.cf`.
- Start the stack only after DNS and TLS are ready.
- Create DKIM keys and publish the resulting DNS TXT record.
- Test SMTP, submission, IMAP and TLS after startup.
- Confirm relay behavior is restricted to authenticated users.

## 6. File Server Setup

- Generate `file/config.yml`.
- Review each share `validusers` and `writelist`.
- Keep employee access read-only unless explicitly required.
- Place department folders on backed-up storage.

## 7. Application Alignment

- Keep application department roles aligned with Samba users.
- Team leaders should be the only non-admin users with write access to department shares.
- Employees should read the same department folders they can access in the app.
- Configure `MAIL_AUDIT_REPORT_ADMIN_EMAIL` and `MAIL_AUDIT_REPORT_RECIPIENTS` before enabling weekly report automation.

## 8. Weekly Audit Report Automation

- Verify the weekly report command locally with `php bin/send-weekly-audit-report.php --dry-run`.
- Use `infra/scripts/send-weekly-audit-report.sh` as the cron target instead of embedding long PHP commands directly.
- Render host-ready assets with:
  - `infra/scripts/render-weekly-audit-report-systemd.sh /tmp/systemd`
  - `infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report`
- Or install them directly with:
  - `sudo infra/scripts/install-weekly-audit-report-systemd.sh /etc/systemd/system www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00"`
  - `sudo infra/scripts/install-weekly-audit-report-cron.sh /etc/cron.d/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log`
- If you stay on the render-only flow, copy rendered files manually into `/etc/systemd/system/` or `/etc/cron.d/` after reviewing host-specific values.
- Redirect cron stdout/stderr to a dedicated log file for delivery troubleshooting.
- Keep the cron schedule aligned with the reporting expectation, for example every Monday morning.
- For systemd, run `systemctl daemon-reload` and `systemctl enable --now verwaltung-weekly-audit-report.timer`.

## 9. Verification

- Run `infra/scripts/preflight-internal-services.sh`.
- Run `infra/scripts/start-internal-services.sh`.
- Check container state with `infra/scripts/check-internal-services.sh`.
- Send a test mail between two internal accounts.
- Mount the Samba share from an internal client and verify read/write behavior by role.
- Run the weekly audit report command once manually and confirm recipients receive the report.

## 10. Hardening

- Enable host firewall and fail2ban where appropriate.
- Back up mail data, configs and shares daily.
- Rotate bootstrap passwords after first login.
- Monitor mail queue, disk usage and certificate expiry.
- Periodically review SPF, DKIM, DMARC and PTR health.
