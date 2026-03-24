## 118. Add Service Health Indicators

1. `Infrastruktur` sayfasina mail ve file servisleri icin canli health indicator eklendi.
2. Health hesaplama `InfrastructureService` icine tasindi.
3. Mail servisi icin SMTP ve demo modunda MailHog UI, internal modda IMAP kontrolu eklendi.
4. File servisi icin Samba portu ve share root varligi kontrolu eklendi.
5. Sonuc `Healthy`, `Degraded` veya `Down` olarak badge seklinde gorunur hale getirildi.
6. Her servis kartinda alt seviyede check bazli durumlar da listelenmeye baslandi.
