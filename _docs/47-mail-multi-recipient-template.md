# 47. Mail Multi Recipient Template

1. `app/Services/MailService.php` coklu alici, HTML/text MIME govdesi ve ek dosya destegi alacak sekilde genislestirildi.
2. `app/Services/InternalMailService.php` ic mail gonderiminde birden fazla kullanici secimi, template render ve optional attachment akisi ile guncellendi.
3. `resources/views/mail/templates/internal-message-text.php` ve `resources/views/mail/templates/internal-message-html.php` dosyalari ile ortak mail template yapisi eklendi.
