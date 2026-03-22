# 21 Demo Infrastructure

## Yapilan Islem

Probe/demo ortam icin ayri altyapi baslatma katmani eklendi.

## Eklenen veya Guncellenen Parcalar

- `.env.example`
- `config/app.php`
- `infra/demo/compose.demo-services.yml`
- `infra/scripts/generate-demo-certs.sh`
- `infra/scripts/start-demo-services.sh`
- `infra/DEMO-README.md`

## Sonuc

- Demo mod icin self-signed sertifika uretilebiliyor.
- Demo stack tek script ile baslatilabiliyor.
- Uygulama config tarafinda demo mod bilgisi tasiniyor.
