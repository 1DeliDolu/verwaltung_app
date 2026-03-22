# 11 Infrastructure Deployment Assets

## Yapilan Islem

Gercek mail server ve file server kurulumu icin repo icine deployment dosyalari eklendi.

## Eklenen veya Guncellenen Parcalar

- `.gitignore`
- `infra/compose.internal-services.yml`
- `infra/.env.internal-services.example`
- `infra/mail/docker-data/dms/config/postfix-accounts.cf.example`
- `infra/mail/docker-data/dms/config/postfix-virtual.cf`
- `infra/mail/docker-data/dms/config/dovecot.cf`
- `infra/file/config.yml.example`
- `infra/scripts/bootstrap-file-shares.sh`

## Sonuc

- `docker-mailserver` icin compose ve config iskeleti var.
- Samba file server icin compose ve share config iskeleti var.
- Departman klasorlerini olusturan bootstrap script hazir.
