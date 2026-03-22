# 15 Secret Generation

## Yapilan Islem

Mail ve file server icin local secret/config dosyalarini otomatik ureten script eklendi.

## Eklenen veya Guncellenen Parcalar

- `infra/scripts/generate-internal-secrets.sh`

## Sonuc

- `.env.internal-services`
- `postfix-accounts.cf`
- `file/config.yml`

tek script ile local olarak uretilebiliyor.
