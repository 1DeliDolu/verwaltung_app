## 100. Open Uploaded Department Files

1. Departman dosya alanina yuklenen dosyalarin yetkili departman kullanicilari tarafindan tarayicida acilabilmesi saglandi.
2. Departman sayfasindaki `Filesystem` tablosuna her dosya icin `Oeffnen` aksiyonu eklendi.
3. Yeni bir guvenli route ile dosya erisimi sadece ilgili departmani gormeye yetkili kullanicilar icin acildi.
4. Dosya okuma akisi path traversal riskine karsi mevcut departman klasoru icinde sinirli kalacak sekilde korundu.
5. Dosya response'u `inline` olarak dondugu icin PDF, resim ve metin gibi desteklenen dosyalar tarayicida acilip okunabilir hale geldi.
6. HR personel dosyalarinin erisimi bu adimda genisletilmedi; Datenschutz nedeniyle mevcut korumali akis korundu.
