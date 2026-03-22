# Rollout Steps

## Phase 1: Prepare

1. Set the production domain and public IP.
2. Create DNS records from `examples/dns-mail-records.example.txt`.
3. Make sure the provider can set reverse DNS.
4. Obtain TLS certificates for the mail hostname.

## Phase 2: Generate Local Files

1. Run `infra/scripts/generate-internal-secrets.sh`.
2. Review generated passwords and store them securely.
3. Review `infra/file/config.yml`.
4. Adjust Samba users and department shares if needed.

## Phase 3: Preflight

1. Copy real certificates to `infra/mail/certs/`.
2. Run `infra/scripts/preflight-internal-services.sh`.
3. Confirm ports are free and compose config parses.

## Phase 4: Start Services

1. Run `infra/scripts/start-internal-services.sh`.
2. Confirm both containers are healthy with `infra/scripts/check-internal-services.sh`.
3. Generate DKIM for the production domain if not already done.
4. Publish the DKIM TXT record.

## Phase 5: Verify Access

1. Log in to the PHP app as admin.
2. Verify department documents match the file share structure.
3. Test Teamleiter write access on the Samba share.
4. Test employee read-only access on the same share.
5. Send and receive test mail with two internal accounts.

## Phase 6: Harden

1. Rotate initial passwords.
2. Back up `mail-data`, `mail-state`, config files and `file/shares`.
3. Monitor mail delivery, certificate renewal and disk growth.
