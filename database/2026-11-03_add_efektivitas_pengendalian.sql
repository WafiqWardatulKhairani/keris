BEGIN;

ALTER TABLE pemantauan_risiko
ADD COLUMN IF NOT EXISTS efektivitas_pengendalian TEXT;

COMMIT;