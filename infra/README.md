# Internal Services Deployment

This folder contains deployment assets for an internal mail server and an internal file server.

## Scope

- Mail server: SMTP + IMAP for company accounts
- File server: Samba shares for department folders
- Access model:
  - admins manage everything
  - team leaders manage their department shares and documents
  - employees read approved department files

## Files

- `compose.internal-services.yml`: Docker Compose stack for mail and file services
- `.env.internal-services.example`: environment template
- `mail/docker-data/dms/config/postfix-accounts.cf.example`: mail account template
- `file/config.yml.example`: Samba users and shares template
- `scripts/bootstrap-file-shares.sh`: creates department folder structure

## Setup

1. Copy `.env.internal-services.example` to `.env.internal-services`.
2. Copy `mail/docker-data/dms/config/postfix-accounts.cf.example` to `mail/docker-data/dms/config/postfix-accounts.cf`.
3. Copy `file/config.yml.example` to `file/config.yml`.
4. Replace all placeholder passwords.
5. Provide TLS certificates under `mail/certs/` matching `MAIL_FQDN`.
6. Run `./scripts/bootstrap-file-shares.sh`.
7. Start the stack with:

```bash
cd infra
docker compose --env-file .env.internal-services -f compose.internal-services.yml up -d
```

## Mail Server Notes

- The stack uses `docker-mailserver`.
- Mailboxes are defined in `postfix-accounts.cf`.
- Aliases can be added in `postfix-virtual.cf`.
- DNS must point the mail hostname to the target server.
- SPF, DKIM, and DMARC should be configured before production use.

## File Server Notes

- The stack uses a Samba container.
- Department shares are defined in `file/config.yml`.
- Team leaders should be added to the share `writelist`.
- Employees should remain read-only unless explicitly required otherwise.
- Recommended hybrid mapping for this workspace:
  - `teamlead-it` / `employee-it` for the IT share
  - `teamlead-hr` / `employee-hr` for the HR share
  - `teamlead-operations` / `employee-operations` for the Operations share
- App logins and Samba logins remain separate credentials by design, but they should match the same department role model.

## Operational Recommendations

- Keep this stack on a private VLAN or VPN-only network.
- Back up mail data and file shares daily.
- Restrict SMTP relay to internal users.
- Rotate all bootstrap passwords after first deployment.
- Monitor disk usage for `/var/mail` and `/samba`.
