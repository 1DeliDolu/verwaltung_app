## 108. Hybrid Services Wrapper Script

1. `infra/scripts/start-hybrid-services.sh` adli yeni bir wrapper script eklendi.
2. Script tek komutla iki modu destekler:
   - `demo`
   - `internal`
3. `demo` modu mevcut `start-demo-services.sh` akisini cagirir.
4. `internal` modu mevcut `start-internal-services.sh` akisini cagirir.
5. Boylece kullanici tum `.yml` dosyalarini korlemesine calistirmak yerine dogru compose akisini secen tek bir giris noktasina sahip oldu.
6. `infra/README.md` ve `infra/DEMO-README.md` icine bu ortak wrapper script kullanim bilgisi eklendi.
