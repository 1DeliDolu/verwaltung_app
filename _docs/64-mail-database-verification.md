# 64. Mail Database Verification

1. Yeni mail migration dosyalari gercek `verwaltung_app` veritabanina uygulandi.
2. Internal mail gonderimi sonrasi `internal_mails`, `internal_mail_recipients` ve `internal_mail_attachments` tablolarinda kayit olustugu dogrulandi.
3. `search` + `scope` ile sender, recipient ve content aramasi basarili calisti; attachment indirme endpoint'i dogru dosya icerigi dondurdu.
