# 38 Internal Mail UI

## Yapilan Islem

Header ve uygulama icine ic mail sayfasi eklendi.

## Eklenen veya Guncellenen Parcalar

- `app/Controllers/InternalMailController.php`
- `routes/web.php`
- `resources/views/partials/header.php`
- `resources/views/mail/index.php`

## Sonuc

- Header icinde `Mail` linki gorunur oldu.
- Kullanicilar `/mail` sayfasinda ic alici dizinini gorebilir.
- Gelen ve giden mesajlar MailHog API uzerinden listelenir.
