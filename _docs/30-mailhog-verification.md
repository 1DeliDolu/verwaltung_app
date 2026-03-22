# 30 MailHog Verification

## Yapilan Islem

Demo/probe ortamda MailHog tabanli mail akisı dogrulandi.

## Uygulanan Adimlar

1. Demo env dosyasi `mailhog`, `1025`, `8025` degerleriyle uretildi.
2. Demo stack `infra/scripts/start-demo-services.sh` ile baslatildi.
3. `http://127.0.0.1:8025/` adresinden MailHog arayuzu dogrulandi.
4. `docker compose ... ps` ile MailHog ve Samba container durumlari kontrol edildi.
5. Demo stack tekrar kapatildi.

## Sonuc

- Probe ortamda gercek mail server yerine MailHog calisiyor.
- SMTP capture `1025`, web arayuzu `8025` uzerinde hazir.
- Samba demo stack icin `1445` host portuna alindi.
