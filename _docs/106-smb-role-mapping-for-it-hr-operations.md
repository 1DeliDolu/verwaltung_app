## 106. SMB Role Mapping For IT HR Operations

1. Samba file server konfigrasyonu IT, HR ve Operations bolumleri icin rol bazli hesaplarla genisletildi.
2. `infra/file/config.yml.example` icine `teamlead-hr`, `employee-hr`, `teamlead-operations` ve `employee-operations` kullanicilari eklendi.
3. Gercek `infra/file/config.yml` dosyasi da ayni rol yapisina uygun olacak sekilde guncellendi.
4. HR share icin `teamlead-hr` yazma, `employee-hr` okuma yetkisi aldi.
5. Operations share icin `teamlead-operations` yazma, `employee-operations` okuma yetkisi aldi.
6. `infra/README.md` ve web-dateibrowser ekraninda uygulama rolleri ile SMB rolleri arasindaki hibrit esleme aciklandi.
7. Boylece proje kullanicilari ile Samba paylasim modeli ayni departman-rol mantiginda hizalanmis oldu.
