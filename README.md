# JAMS Certificate Generator — Vercel Ready

## Deploy
1. Upload this folder to a GitHub repository.
2. In Vercel, import the repository.
3. Framework Preset: Other / Static.
4. Build Command: leave empty.
5. Output Directory: `.`
6. Deploy.

## Features
- Existing JAMS A4 landscape certificate generator.
- PDF download and print.
- Auto certificate ID.
- Certificate verification link generator.
- Public `verify.html` verification page.
- No server/database required for this first version.

## Important
The current verification URL carries the certificate data in the URL. It is convenient for deployment but is **not cryptographically signed**. For production-grade verification, add a database + server-side signing/lookup layer (e.g. Supabase/Postgres or another managed database) before treating the verification result as tamper-proof.
