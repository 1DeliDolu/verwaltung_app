## 110. Hybrid Services Stop Wrapper Script

1. `infra/scripts/stop-hybrid-services.sh` adli yeni bir wrapper script eklendi.
2. Script tek komutla iki modu destekler:
   - `demo`
   - `internal`
3. `demo` modunda demo compose stack'i `down` ile durdurulur.
4. `internal` modunda internal compose stack'i `down` ile durdurulur.
5. Boylece start wrapper script'ine karsilik gelen simetrik bir stop giris noktasi saglandi.
6. `infra/README.md` ve `infra/DEMO-README.md` icine durdurma komutu da eklendi.
