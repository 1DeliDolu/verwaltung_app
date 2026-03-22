# 29 Demo Samba Port

## Yapilan Islem

Demo stack icin Samba host portu standart `445` yerine `1445` olarak ayarlandi.

## Eklenen veya Guncellenen Parcalar

- `infra/demo/compose.demo-services.yml`

## Sonuc

- Probe ortamda host uzerindeki mevcut SMB servisleriyle cakismaz.
- Container icinde Samba yine `445` uzerinden calisir.
