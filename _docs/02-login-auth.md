# 02 Login Database

## Yapilan Islem

Bu asamada login akisinin kullanacagi veritabani yapisi ve varsayilan admin kaydi hazirlandi.

## Eklenen veya Guncellenen Parcalar

- `database/migrations/001_create_users_table.sql`
- `database/seeds/003_admin_user.sql`

## Sonuc

- `users` tablosu icin SQL migration yazildi.
- Login icin kullanilacak `password_hash` alani tanimlandi.
- Varsayilan admin kullanicisi seed olarak eklendi.
- Login akisinin kullanacagi temel veri hazirlandi.

## Varsayilan Giris Bilgisi

- E-posta: `admin@verwaltung.local`
- Sifre: `D0cker!123`
