## 107. SMB Role Mapping For IT HR Operations Verification

1. `php -l resources/views/services/fileserver.php` calistirildi.
2. `infra/file/config.yml` elle incelendi ve yeni SMB kullanicilarinin `auth` altinda yer aldigi dogrulandi.
3. `infra/file/config.yml.example` elle incelendi ve ayni rol bazli ornek yapinin yer aldigi dogrulandi.
4. `HR` share icin `validusers: admin teamlead-hr employee-hr` ve `writelist: admin teamlead-hr` tanimlandigi dogrulandi.
5. `Operations` share icin `validusers: admin teamlead-operations employee-operations` ve `writelist: admin teamlead-operations` tanimlandigi dogrulandi.
6. `infra/README.md` icinde hibrit model ve rol eslemesi notunun eklendigi dogrulandi.
7. Web-dateibrowser sayfasinda `SMB Rollenmapping` bilgisinin gosterildigi dogrulandi.
