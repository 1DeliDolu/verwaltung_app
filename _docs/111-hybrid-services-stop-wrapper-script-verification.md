## 111. Hybrid Services Stop Wrapper Script Verification

1. `bash -n infra/scripts/stop-hybrid-services.sh` calistirildi.
2. Script'in `demo` ve `internal` modlari icin ilgili compose `down` komutlarini cagdirdigi dogrulandi.
3. Yanlis parametre icin kullanim yardimi bastigi dogrulandi.
4. `infra/README.md` icinde `infra/scripts/stop-hybrid-services.sh internal` kullaniminin belgelendigi dogrulandi.
5. `infra/DEMO-README.md` icinde `infra/scripts/stop-hybrid-services.sh demo` kullaniminin belgelendigi dogrulandi.
