## 84. Calendar Delete Action

1. Takvim event modeli icin fiziksel silme metodu eklendi.
2. `Delete` aksiyonu sadece event sahibi veya `admin` kullaniciya acik bir servis katmani ile korundu.
3. Yeni `/calendar/events/{id}/delete` endpoint'i controller ve route tarafina eklendi.
4. Takvim kartlarinda yetkili kullaniciya `Edit`, `Delete`, `Erledigt` butonlari birlikte gosteriliyor.
5. `Delete` islemi onay penceresi sonrasinda event kaydini ve departman pivot kayitlarini veritabanindan siliyor.
