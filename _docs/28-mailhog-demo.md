# 28 MailHog Demo

## Yapilan Islem

Demo/probe mail katmani gercek mail server yerine MailHog olarak degistirildi.

## Eklenen veya Guncellenen Parcalar

- `infra/demo/compose.demo-services.yml`
- `infra/scripts/generate-demo-env.sh`
- `infra/scripts/start-demo-services.sh`
- `infra/DEMO-README.md`

## Sonuc

- Demo ortamda SMTP capture icin MailHog kullaniliyor.
- MailHog SMTP portu `1025`, web arayuzu `8025`.
- Probe senaryosu icin gereksiz TLS ve gercek mail teslimati beklentisi kaldirildi.
