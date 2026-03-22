## 80. Calendar Event Editing

1. `calendar_events` kayitlari icin aktif event yukleme ve update metotlari eklendi.
2. Sadece event sahibi veya `admin` rolu olan kullanici duzenleme yapabilecek sekilde yetki kontrolu yazildi.
3. Takvim formu create ve edit modlari arasinda calisacak sekilde guncellendi.
4. Event kartlarina `Bearbeiten` aksiyonu eklendi.
5. Edit modunda form alanlari mevcut event verisiyle dolduruluyor ve submit `/calendar/events/{id}/update` endpoint'ine gidiyor.
6. Update sonrasi secilen departmanlar tekrar esleniyor ve etiketli birimler icin `Termin aktualisiert:` konusu ile ic mail uretiliyor.
